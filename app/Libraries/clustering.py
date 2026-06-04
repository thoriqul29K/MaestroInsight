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
from pathlib import Path

import numpy as np
import pandas as pd
from scipy.cluster.hierarchy import linkage, fcluster
from sklearn.preprocessing import MinMaxScaler

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


def hierarchical_cluster(features, n_clusters=5, method='ward', metric='euclidean'):
    """Hierarchical clustering agglomerative."""
    Z = linkage(features, method=method, metric=metric)
    labels = fcluster(Z, t=n_clusters, criterion='maxclust')
    return Z, labels


def label_segments(df, labels, n_clusters=5):
    """Label cluster menjadi nama segmen berdasarkan profil RFM."""
    df = df.copy()
    df['cluster_raw'] = labels

    cluster_means = df.groupby('cluster_raw')[['recency', 'frequency', 'monetary']].mean()

    sorted_by_monetary = cluster_means.sort_values('monetary', ascending=False).index.tolist()

    segment_names = {
        'loyal':     'Loyal',
        'potential': 'Potential',
        'budget':    'Budget Hunter',
        'seasonal':  'Seasonal',
        'at_risk':   'At Risk',
    }

    if len(sorted_by_monetary) < n_clusters:
        for i in range(len(sorted_by_monetary), n_clusters):
            sorted_by_monetary.append(i)

    n = len(sorted_by_monetary)

    loyal_idx       = sorted_by_monetary[0]
    potential_idx   = sorted_by_monetary[1] if n > 1 else loyal_idx
    at_risk_idx     = sorted_by_monetary[-1]
    budget_idx      = sorted_by_monetary[-2] if n > 2 else at_risk_idx
    seasonal_idx    = sorted_by_monetary[2] if n > 3 else (sorted_by_monetary[2] if n == 4 else at_risk_idx)

    if n == 4:
        mapping = {
            sorted_by_monetary[0]: 'loyal',
            sorted_by_monetary[1]: 'potential',
            sorted_by_monetary[2]: 'seasonal',
            sorted_by_monetary[3]: 'at_risk',
        }
    elif n == 3:
        mapping = {
            sorted_by_monetary[0]: 'loyal',
            sorted_by_monetary[1]: 'potential',
            sorted_by_monetary[2]: 'at_risk',
        }
    elif n == 2:
        mapping = {
            sorted_by_monetary[0]: 'loyal',
            sorted_by_monetary[1]: 'at_risk',
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
    return df[['id_pelanggan', 'segment']]


def write_output(df, path):
    """Tulis hasil segmentasi ke CSV."""
    df.to_csv(path, index=False)


def main():
    """Entry point.

    Mode 1 (CSV round-trip):
        python clustering.py <input_csv> <output_csv>

    Mode 2 (Direct DB):
        python clustering.py --db <input_csv> <output_csv>
    """
    if len(sys.argv) < 3:
        print(json.dumps({
            'status': 'error',
            'message': 'Usage: clustering.py <input_csv> <output_csv> atau --db <input_csv> <output_csv>'
        }))
        sys.exit(1)

    args = sys.argv[1:]

    use_db = False
    if args[0] == '--db':
        use_db = True
        args = args[1:]

    input_csv  = args[0]
    output_csv = args[1]

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
            print(json.dumps({'status': 'empty', 'count': 0}))
            write_output(pd.DataFrame(columns=['id_pelanggan', 'segment']), output_csv)
            sys.exit(0)

        features, _ = normalize(df)
        _, labels = hierarchical_cluster(features, n_clusters=5)
        result = label_segments(df, labels, n_clusters=5)
        write_output(result, output_csv)

        summary = result['segment'].value_counts().to_dict()
        print(json.dumps({
            'status': 'ok',
            'count': len(result),
            'segments': summary,
        }, indent=2))
        sys.exit(0)

    except Exception as e:
        print(json.dumps({
            'status': 'error',
            'message': str(e),
        }))
        sys.exit(1)


if __name__ == '__main__':
    main()
