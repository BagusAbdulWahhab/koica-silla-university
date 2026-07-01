import socket
import threading
import json
import time

from common import (
    send_json
)

HOST = "127.0.0.1"
PORT = 9000

NAME = "Eve"
IP = "192.168.1.20"
MAC = "EE"

DNS_POISONING = False

FAKE_DNS_RECORDS = {
    "bank.com": "192.168.1.250",
    "youtube.com": "192.168.1.250"
}

known_hosts = {}
victim_ip = None
gateway_ip = None
spoofing = False
forwarding_enabled = True

def receive_loop(sock):
    global forwarding_enabled, spoofing, victim_ip, gateway_ip
    buffer = ""
    while True:
        try:
            data = sock.recv(1024)
            if not data:
                break
            buffer += (data.decode())
            while (
                "\n"
                in buffer
            ):

                line, buffer = (
                    buffer.split(
                        "\n",
                        1
                    )
                )
                if (not line.strip()):
                    continue
                message = (json.loads(line))

                print(
                    f"\n[{NAME}] "
                    f"Received:"
                )

                msg_type = message["type"]
                if msg_type == "UNICAST" and spoofing:
                    destination_mac  = message.get("destination_mac")
                    if destination_mac == MAC:
                        payload = message.get("payload", {})
                        payload_type = payload.get("type", "UNKNOWN")
                        if(DNS_POISONING and payload_type == "DNS_QUERY"):
                            domain = payload["domain"]
                            if(domain in FAKE_DNS_RECORDS):
                                victim_mac = known_hosts[victim_ip]
                                fake_ip = (FAKE_DNS_RECORDS[domain])
                                print("\n[Eve] DNS POISONING!")
                                print(
                                    f"{domain} -> {fake_ip}" 
                                )
                                send_json(
                                    sock,
                                    {
                                        "type": "UNICAST",
                                        "sender_mac": MAC,
                                        "destination_mac": victim_mac,
                                        "payload": {
                                            "type": "DNS_REPLY",
                                            "domain": domain,
                                            "ip": fake_ip
                                        },
                                    }
                                )
                                continue
                        print(f"\n[Eve] Intercepted: ")
                        print(payload_type)
                        forward_packet(
                            sock,
                            message
                        )
                        continue

                if(msg_type == "ARP_REQUEST"):
                    sender_ip = message["sender_ip"]
                    sender_mac = message["sender_mac"]
                    known_hosts[sender_ip] = sender_mac
                    print(f"\n[Eve] Learned: ")
                    print(f"{sender_ip} -> {sender_mac}")
                    target_ip = message["target_ip"]
                    print(f"[Eve] Observed ARP Request")
                    print(f"Who has {target_ip}")

                elif msg_type == "UNICAST":
                    payload = message["payload"]
                    if payload["type"] == "ARP_REPLY":
                        sender_ip = payload["sender_ip"]
                        sender_mac = payload["sender_mac"]
                        known_hosts[sender_ip] = sender_mac
                        print(f"\n[Eve] Learned")
                        print(f"{sender_ip} -> {sender_mac}")

                elif msg_type == "ARP_REPLY":
                    sender_ip = message["sender_ip"]
                    sender_mac = message["sender_mac"]
                    known_hosts[sender_ip] = sender_mac
                    print(f"\n[Eve] Learned")
                    print(f"{sender_ip} -> {sender_mac}") 

        except:
            break

def forward_packet(sock, message):
    global forwarding_enabled, victim_ip, gateway_ip
    if not forwarding_enabled:
        return

    sender_mac = message["sender_mac"]
    victim_mac = known_hosts.get(victim_ip)
    gateway_mac = known_hosts.get(gateway_ip)
    if sender_mac == victim_mac:
        print("\n[Eve] Forwarding packet:")
        print("Alice → Router")
        message["destination_mac"] = gateway_mac
        send_json(sock,message)

    elif sender_mac == gateway_mac:
        print("\n[Eve] Forwarding packet:")
        print("Router → Alice")
        message["destination_mac"] = victim_mac
        send_json(sock, message)

def arp_scan(sock):
    print("\n[Eve] Starting ARP Discovery...")
    subnet = "192.168.1."
    for host in range(1, 255):
        target_ip = f"{subnet}{host}"
        send_json(
            sock,
            {
                "type": "ARP_REQUEST",
                "sender_ip": IP,
                "sender_mac": MAC,
                "target_ip": target_ip
            }
        )
        time.sleep(5)
    print("[Eve] Discovery packets sent.")

def main():
    global spoofing, victim_ip, gateway_ip, forwarding_enabled, DNS_POISONING
    sock = socket.socket(
        socket.AF_INET,
        socket.SOCK_STREAM
    )
    sock.connect((HOST, PORT))
    send_json(
        sock,
        {
            "type":"REGISTER",
            "name":NAME,
            "ip":IP,
            "mac":MAC
        }
    )

    threading.Thread(
        target=
            receive_loop,
            args=(sock,),
            daemon=True
    ).start()
    threading.Thread(
        target=
            arp_spoof_loop,
            args=(sock,),
            daemon=True
    ).start()
    print(
        "[Eve] "
        "Connected"
    )
    print(
    "[Eve] Waiting..."
    )
    while True:
        print("\n1. ARP Discovery")
        print("2. Show Known Hosts")
        print("3. Select Victim")
        print("4. Select Gateway")
        print("5. Start ARP Spoof")
        print("6. Stop ARP Spoof")
        print("7. Toggle Forwarding")
        print("8. Toggle DNS Poisoning")

        choice = input(">> ")
        if choice == "1":
            threading.Thread(
                target=arp_scan,
                args=(sock,),
                daemon=True
            ).start()
        elif choice == "2":
            print("\nKnown Hosts: ")
            for ip, mac in known_hosts.items():
                print(f"{ip} -> {mac}")
        elif choice == "3":
            victim_ip = input("Victim IP: ")
            if victim_ip not in known_hosts:
                print("Unknown Host")
                victim_ip = None
            else:
                print(f"Victim Selected: ")
                print(f"{victim_ip}")    
        elif choice == "4":
            gateway_ip = input("Gateway IP: ")
            if gateway_ip not in known_hosts:
                print("Unknown Host.")
                gateway_ip = None
            else:
                print(
                    f"Gateway Selected: "
                    f"{gateway_ip}"
                )          
        elif choice == "5":
            if victim_ip is None:
                print("Select Victim First")
                continue
            if gateway_ip is None:
                print("Select Victim First")
                continue
            spoofing = True
            print("\nARP Spoofing Started")
        elif choice == "6":
            spoofing = False
            print("\nARP Spoofing Stopped")
        elif choice == "7":
            forwarding_enabled = (not forwarding_enabled)
            print(
                f"\nForwarding: "
                f"{forwarding_enabled}"
            )
        elif choice == "8":
            DNS_POISONING = (not DNS_POISONING)
            print(
                f"\nDNS POISONING: "
                f"{DNS_POISONING}"
            )    

def arp_spoof_loop(sock):
    global spoofing, victim_ip, gateway_ip
    while True:
        if spoofing and victim_ip and gateway_ip:
            victim_mac = known_hosts[victim_ip]
            gateway_mac = known_hosts[gateway_ip]
            print("\n[Eve] Sending spoofed ARP Replies...")
            print(f"To Victim:")
            print(f"{gateway_ip} is at {MAC}")

            send_json(
                sock,
                {
                    "type":"UNICAST",
                    "sender_mac":MAC,
                    "destination_mac":victim_mac,
                    "payload":{
                        "type":"ARP_REPLY",
                        "sender_ip":gateway_ip,
                        "sender_mac":MAC
                    }
                }
            )
            print(f"To Router:")
            print(f"{victim_ip} is at {MAC}")
            send_json(
                sock,
                {
                    "type":"UNICAST",
                    "sender_mac":MAC,
                    "destination_mac":gateway_mac,
                    "payload":{
                        "type":"ARP_REPLY",
                        "sender_ip":victim_ip,
                        "sender_mac":MAC
                    }
                }
            )
        time.sleep(10)           

if __name__ == "__main__":
    main()