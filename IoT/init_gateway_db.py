import sqlite3

DB_NAME = "gateway.db"

conn = sqlite3.connect(DB_NAME)
cursor = conn.cursor()

cursor.execute("""
CREATE TABLE IF NOT EXISTS devices (
    id TEXT PRIMARY KEY,
    description TEXT,
    gw_id TEXT
)
""")

cursor.execute("""
CREATE TABLE IF NOT EXISTS device_data (
    id TEXT PRIMARY KEY,
    device_id TEXT,
    temp REAL,
    hum REAL,
    lux REAL,
    noise REAL,
    timestamp TEXT
)
""")

conn.commit()
conn.close()

print("Gateway database initialized")