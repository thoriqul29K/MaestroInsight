FROM python:3.11-slim

ENV PYTHONDONTWRITEBYTECODE=1 \
    PYTHONUNBUFFERED=1 \
    PIP_NO_CACHE_DIR=1 \
    PIP_DISABLE_PIP_VERSION_CHECK=1

WORKDIR /app

RUN apt-get update && apt-get install -y --no-install-recommends \
        ca-certificates \
        gcc \
    && rm -rf /var/lib/apt/lists/*

COPY docker/clustering/requirements.txt /app/requirements.txt
RUN pip install -r /app/requirements.txt

COPY app/Libraries/clustering.py /app/clustering.py

RUN mkdir -p /data && chmod 0777 /data

CMD ["tail", "-f", "/dev/null"]