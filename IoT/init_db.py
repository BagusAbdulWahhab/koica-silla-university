import sqlite3

DB_NAME = 'koica-iot.db'

def init_db():
	conn = sqlite3.connect(DB_NAME)
	cur = conn.cursor()
	# Create table if not exists
	cur.execute('''
		CREATE TABLE IF NOT EXISTS devices (
			id TEXT PRIMARY KEY,
			description TEXT,
			gw_id TEXT
		)
	''')
	# Create device_data table if not exists
	cur.execute('''
		CREATE TABLE IF NOT EXISTS device_data (
			id TEXT PRIMARY KEY,
			device_id TEXT,
			temp REAL,
			hum REAL,
			lux REAL,
			noise REAL,
			timestamp TEXT,
			FOREIGN KEY(device_id) REFERENCES devices(id)
		)
	''')
	# Upsert data
	devices = [
		{"id": "node_D1109", "description": "cloud-big data classroom", "gw_id": "gw_001"},
		{"id": "node_D1110", "description": "smart factory classroom", "gw_id": "gw_002"},
		{"id": "node_D1111", "description": "smart factory classroom", "gw_id": "gw_001"},
		{"id": "node_D1112", "description": "smart factory classroom", "gw_id": "gw_001"}

	]
	for device in devices:
		cur.execute('''
			INSERT INTO devices (id, description, gw_id)
			VALUES (?, ?, ?)
			ON CONFLICT(id) DO UPDATE SET description=excluded.description
		''', (device["id"], device["description"], device["gw_id"]))
	conn.commit()
	conn.close()

if __name__ == "__main__":
	init_db()
