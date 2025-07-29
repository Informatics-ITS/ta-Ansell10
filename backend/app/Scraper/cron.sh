#!/bin/sh

# Aktifkan virtual-env Python
source /opt/venv/bin/activate

log() {
  echo "[$(date '+%Y-%m-%d %H:%M:%S')] $*"
}

log "Scraper run START"
python detikhealth.py
STATUS=$?

if [ "$STATUS" -eq 0 ]; then
  log "Scraper run DONE (exit 0)"
else
  log "Scraper run ERROR (exit $STATUS)"
fi
