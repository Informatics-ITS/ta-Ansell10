#!/usr/bin/env python
# -*- coding: utf-8 -*-

import os, time, re, requests, mysql.connector
from bs4 import BeautifulSoup
from urllib.parse import urlsplit, urlunsplit
from dotenv import load_dotenv

# ------------------------------------------------------------------
# 1) ENV & HEADERS
# ------------------------------------------------------------------
dotenv_path = os.path.join(os.path.dirname(__file__), '..', '..', '.env')
load_dotenv(dotenv_path)

headers = {"User-Agent": "Mozilla/5.0"}

# ------------------------------------------------------------------
# 2)  HELPER : tag & activity detector  (fungsi lama dipertahankan)
# ------------------------------------------------------------------
def detect_activity_level(text: str) -> str:
    text = text.lower()
    if any(k in text for k in ["duduk", "tidak aktif", "rebahan"]):
        return "sedentary"
    if any(k in text for k in ["jalan kaki", "pekerjaan rumah", "aktivitas ringan"]):
        return "light"
    if any(k in text for k in ["jogging", "bersepeda", "aktivitas sedang"]):
        return "moderate"
    if any(k in text for k in ["lari", "angkat beban", "latihan", "olahraga intens"]):
        return "active"
    if any(k in text for k in ["atlet", "buruh", "fisik berat"]):
        return "very active"
    return "light"


TAG_KEYWORDS = {
    "Gizi":      ["gizi", "nutrisi", "makan", "diet", "serat", "kalori", "vitamin", "mineral"],
    "Olahraga":  ["olahraga", "latihan", "lari", "workout", "senam", "bersepeda", "aktivitas fisik"],
    "Kesehatan": ["sehat", "kesehatan", "sakit", "penyakit", "dokter", "rumah sakit", "gejala"],
    "Kebugaran": ["kebugaran", "fit", "kebugaran jasmani", "fitness"],
    "Mental":    ["mental", "psikolog", "stress", "depresi", "cemas", "jiwa", "kecemasan"],
    "Seks":      ["seks", "hubungan seksual", "reproduksi", "kehamilan", "kontrasepsi", "menstruasi"],
    "Obat":      ["obat", "tablet", "suplemen", "resep", "pengobatan"],
    "Anak":      ["anak", "balita", "bayi", "remaja", "ibu hamil"],
}

def detect_tag(title: str, content: str) -> str:
    text = f"{title} {content}".lower()
    for tag, kws in TAG_KEYWORDS.items():
        if any(kw in text for kw in kws):
            return tag
    return "Umum"


def clean_content(raw: str) -> str:
    lines, out = raw.splitlines(), []
    for line in (l.strip() for l in lines):
        if not line or "ADVERTISEMENT" in line:        continue
        if line.lower().startswith("baca juga"):       continue
        if any(s in line.lower() for s in ["video olahraga", "video "]): continue
        if re.match(r'^\([a-z]{2,5}/[a-z]{2,5}\)$', line):       continue
        if all(w.islower() for w in line.split()) and len(line.split()) < 8:  continue
        if re.search(r'(video|cek outfit|kalcer runner|mitos|fakta|kata dokter|waspada!|simak juga|next:)', line.lower()):
            continue
        if len(line) < 55 and re.search(r'(video|waspada|mitos|fakta|next:|kata dokter)', line.lower()):
            continue
        out.append(line)
    return "\n".join(out).strip()

# ------------------------------------------------------------------
# 3)  DB connection
# ------------------------------------------------------------------
db = mysql.connector.connect(
    host     = os.getenv("DB_HOST", "localhost"),
    user     = os.getenv("DB_USER", "root"),
    password = os.getenv("DB_PASSWORD", ""),
    database = os.getenv("DB_DATABASE", ""),
    port     = int(os.getenv("DB_PORT", 3306)),
)
cur = db.cursor()


SQL = """
INSERT INTO articles
  (title, summary, content, tag, activity_level,
   image_url, author, source, created_at, updated_at)
VALUES
  (%s, %s, %s, %s, %s, %s, %s, %s, NOW(), NOW())
ON DUPLICATE KEY UPDATE
  summary        = VALUES(summary),
  content        = VALUES(content),
  tag            = VALUES(tag),
  activity_level = VALUES(activity_level),
  image_url      = VALUES(image_url),
  author         = VALUES(author),
  updated_at     = NOW()
"""

# ------------------------------------------------------------------
# 4)  Crawl list-page & build unique URL list
# ------------------------------------------------------------------
kategori_urls = [
    "https://health.detik.com/wellness-diet",
    "https://health.detik.com/kebugaran",
]
links: set[str] = set()

for cat in kategori_urls:
    try:
        soup = BeautifulSoup(requests.get(cat, headers=headers, timeout=15).text, "html.parser")
        found = [
            a["href"] for a in soup.select("a.media_link[href*='/d-'], a.media__link[href*='/d-']")
            if a.get("href", "").startswith("http")
        ]
        links.update(found)
        print(f"{cat}  →  {len(found)} link")
    except Exception as e:
        print(f"[ERR] gagal fetch {cat} : {e}")

print(f"Total unik URL: {len(links)}\n")
SELECT_EXISTS = "SELECT 1 FROM articles WHERE source = %s LIMIT 1"

# ------------------------------------------------------------------
# 5)  Loop tiap artikel
# ------------------------------------------------------------------
for url in links:
    try:
        # — 1) kanonikan URL (buang query, hapus slash akhir) —
        s = urlsplit(url)
        clean_url = urlunsplit((s.scheme, s.netloc, s.path.rstrip("/"), "", ""))

        # — 2) CEK apakah sudah ada di DB —
        cur.execute(SELECT_EXISTS, (clean_url,))
        if cur.fetchone():
            print(f"[SKP]  {clean_url}  (sudah ada)")
            continue                     # langsung lanjut ke URL berikut

        # — 3) scrap detail (hanya untuk URL baru) —
        soup     = BeautifulSoup(
                     requests.get(clean_url, headers=headers, timeout=15).text,
                     "html.parser")
        title    = soup.find("h1").get_text(strip=True)
        content  = clean_content(
                     soup.select_one(".detail__body-text").get_text("\n").strip())
        meta     = soup.find("meta", attrs={"name": "description"})
        summary  = (meta["content"].strip() if meta and meta.get("content") else
                    next((l for l in content.split("\n")
                              if l and len(l) > 40 and not l.lower().startswith("jakarta")),
                         content.split("\n")[0]))
        image_url  = (soup.find("meta", property="og:image") or {}).get("content", "")
        tag        = detect_tag(title, content)
        act_level  = detect_activity_level(content)

        a_block = soup.select_one(".detail__author")
        author  = (a_block.get_text(" ").split("-")[0].strip().strip('"\'')
                   if a_block else "DetikHealth")

        data = (title, summary, content, tag, act_level,
                image_url, author, clean_url)

        cur.execute(SQL, data)           # ← INSERT (takkan duplikat karena di-cek)
        db.commit()
        print(f"[ADD]  {title[:60]}…")

        time.sleep(1)

    except Exception as e:
         print(f"[ERR] gagal scrape {url} : {e}")

# tutup koneksi
cur.close()
db.close()
