#!/usr/bin/env python3
"""
Hierarchical Clustering (Agglomerative) untuk segmentasi pelanggan
PT. Maestro Wisata Raya.

Metode: Ward Linkage, metrik Euclidean, normalisasi Min-Max
Cluster: 5 (Loyal, Potential, Budget Hunter, Seasonal, At Risk)
"""

import sys
import os
import csv
import json
import tempfile
import signal
import time
from pathlib import Path

import numpy as np
import pandas as pd
from scipy.cluster.hierarchy import linkage, fcluster
from sklearn.preprocessing import MinMaxScaler

DEFAULT_TIMEOUT_SECONDS = 300

try:
    import pymysql
    HAS_PYMYSQL = True
except ImportError:
    HAS_PYMYSQL = False


def load_data_from_db(host, port, user, password, database):
    """Load RFM data dari MySQL."""
    if not HAS_PYMYSQL:
        raise RuntimeError("pymysql tidak terinstall. Jalankan: pip install pymysql")
    conn = pymysql.connect(
        host=host, port=int(port), user=user, password=password, database=database,
        cursorclass=pymysql.cursors.DictCursor
    )
    try:
        with conn.cursor() as cur:
            cur.execute("SELECT id_pelanggan, recency, frequency, monetary FROM tb_rfm")
            rows = cur.fetchall()
    finally:
        conn.close()
    return pd.DataFrame(rows)


def load_data_from_csv(path):
    """Load RFM data dari CSV."""
    return pd.read_csv(path)


def normalize(df):
    """Min-Max normalization [0,1]."""
    scaler = MinMaxScaler()
    features = scaler.fit_transform(df[['recency', 'frequency', 'monetary']])
    return features, scaler


def hierarchical_cluster(features, n_clusters=5, method='ward', metric='euclidean', timeout_seconds=DEFAULT_TIMEOUT_SECONDS):
    """Hierarchical clustering agglomerative.

    Menjalankan linkage dengan watchdog berbasis signal (Unix) atau
    thread timer (Windows + Unix fallback). Linkage scipy bersifat
    blocking, jadi pendekatan ini mematikan proses Python ketika
    melewati timeout sehingga PHP tidak freeze di shell_exec.
    """
    n = len(features)

    print(f"[clustering] mulai: n={n}, k={n_clusters}, method={method}, metric={metric}", flush=True)

    if n <= 0:
        return None, np.array([], dtype=int)

    if n > 5000 and method == 'ward':
        print(
            "[clustering] WARNING: dataset besar (n>5000). Hierarchical clustering O(n^2) "
            "mungkin lambat. Pertimbangkan untuk mengambil sample.",
            flush=True,
        )

    timed_out = {'flag': False}

    def _timeout_handler():
        timed_out['flag'] = True
        print("[clustering] ERROR: timeout tercapai, proses dihentikan.", flush=True)
        os._exit(124)

    timer = None
    use_signal = hasattr(signal, 'SIGALRM') and sys.platform != 'win32'

    if use_signal:
        signal.signal(signal.SIGALRM, lambda *_: _timeout_handler())
        signal.alarm(int(timeout_seconds))
    else:
        import threading
        timer = threading.Timer(int(timeout_seconds), _timeout_handler)
        timer.daemon = True
        timer.start()

    try:
        start = time.time()
        Z = linkage(features, method=method, metric=metric)
        elapsed = time.time() - start
        print(f"[clustering] linkage selesai dalam {elapsed:.2f}s", flush=True)
        labels = fcluster(Z, t=n_clusters, criterion='maxclust')
    finally:
        if use_signal:
            signal.alarm(0)
        if timer is not None:
            timer.cancel()

    if timed_out['flag']:
        raise TimeoutError("Hierarchical clustering melebihi batas waktu yang ditentukan")

    return Z, labels


def label_segments(df, labels, features=None, n_clusters=5):
    """Label cluster menjadi nama segmen berdasarkan profil RFM."""
    df = df.copy()
    df['cluster_raw'] = labels

    if features is not None:
        df['recency_norm']   = features[:, 0]
        df['frequency_norm'] = features[:, 1]
        df['monetary_norm']  = features[:, 2]

    df['composite_score'] = (1 - df['recency_norm']) + df['frequency_norm'] + df['monetary_norm']
    composite_means = df.groupby('cluster_raw')['composite_score'].mean()
    sorted_by_composite = composite_means.sort_values(ascending=False).index.tolist()

    if len(sorted_by_composite) < n_clusters:
        for i in range(len(sorted_by_composite), n_clusters):
            sorted_by_composite.append(i)

    n = len(sorted_by_composite)

    loyal_idx       = sorted_by_composite[0]
    potential_idx   = sorted_by_composite[1] if n > 1 else loyal_idx
    at_risk_idx     = sorted_by_composite[-1]
    budget_idx      = sorted_by_composite[-2] if n > 2 else at_risk_idx
    seasonal_idx    = sorted_by_composite[2] if n > 3 else (sorted_by_composite[2] if n == 4 else at_risk_idx)

    if n == 4:
        mapping = {
            sorted_by_composite[0]: 'loyal',
            sorted_by_composite[1]: 'potential',
            sorted_by_composite[2]: 'seasonal',
            sorted_by_composite[3]: 'at_risk',
        }
    elif n == 3:
        mapping = {
            sorted_by_composite[0]: 'loyal',
            sorted_by_composite[1]: 'potential',
            sorted_by_composite[2]: 'at_risk',
        }
    elif n == 2:
        mapping = {
            sorted_by_composite[0]: 'loyal',
            sorted_by_composite[1]: 'at_risk',
        }
    else:
        mapping = {
            loyal_idx:     'loyal',
            potential_idx: 'potential',
            budget_idx:    'budget',
            seasonal_idx:  'seasonal',
            at_risk_idx:   'at_risk',
        }

    df['segment'] = df['cluster_raw'].map(mapping)
    cols = ['id_pelanggan']
    if features is not None:
        cols.extend(['recency_norm', 'frequency_norm', 'monetary_norm'])
    cols.append('segment')
    return df[cols]


def write_output(df, path):
    """Tulis hasil segmentasi ke CSV."""
    df.to_csv(path, index=False)


def main():
    """Entry point.

    Mode 1 (CSV round-trip):
        python clustering.py <input_csv> <output_csv> [--timeout SECONDS]

    Mode 2 (Direct DB):
        python clustering.py --db <input_csv> <output_csv> [--timeout SECONDS]
    """
    if len(sys.argv) < 3:
        print(json.dumps({
            'status': 'error',
            'message': 'Usage: clustering.py <input_csv> <output_csv> [--timeout SECONDS]'
        }))
        sys.exit(1)

    args = sys.argv[1:]

    use_db = False
    timeout_seconds = DEFAULT_TIMEOUT_SECONDS
    if args and args[0] == '--db':
        use_db = True
        args = args[1:]

    while args and args[0] == '--timeout':
        try:
            timeout_seconds = int(args[1])
        except (IndexError, ValueError):
            pass
        args = args[2:]

    if len(args) < 2:
        print(json.dumps({
            'status': 'error',
            'message': 'Argument tidak lengkap. Usage: clustering.py <input_csv> <output_csv> [--timeout SECONDS]'
        }))
        sys.exit(1)

    input_csv  = args[0]
    output_csv = args[1]

    print(f"[clustering] timeout diset: {timeout_seconds}s", flush=True)

    try:
        if use_db:
            host = os.environ.get('DB_HOST', 'localhost')
            port = os.environ.get('DB_PORT', '3306')
            user = os.environ.get('DB_USER', 'root')
            password = os.environ.get('DB_PASSWORD', '')
            database = os.environ.get('DB_NAME', '')
            df = load_data_from_db(host, port, user, password, database)
            df.to_csv(input_csv, index=False)
        else:
            df = load_data_from_csv(input_csv)

        if df.empty:
            print(json.dumps({'status': 'empty', 'count': 0}), flush=True)
            empty = pd.DataFrame(columns=['id_pelanggan', 'recency_norm', 'frequency_norm', 'monetary_norm', 'segment'])
            write_output(empty, output_csv)
            sys.exit(0)

        features, _ = normalize(df)
        _, labels = hierarchical_cluster(
            features,
            n_clusters=5,
            timeout_seconds=timeout_seconds,
        )
        result = label_segments(df, labels, features=features, n_clusters=5)
        write_output(result, output_csv)

        summary = result['segment'].value_counts().to_dict()
        print(json.dumps({
            'status': 'ok',
            'count': len(result),
            'segments': summary,
        }, indent=2), flush=True)
        sys.exit(0)

    except Exception as e:
        print(json.dumps({
            'status': 'error',
            'message': str(e),
        }), flush=True)
        sys.exit(1)


if __name__ == '__main__':
    main()
