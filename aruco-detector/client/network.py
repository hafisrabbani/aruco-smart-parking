import requests

def send_tag_data(tag_id):
    try:
        response = requests.post(
            "http://127.0.0.1:8000/api/entry-gate",
            json={"id": tag_id},
            timeout=5
        )
        if response.status_code == 200:
            return True, response.json()
        return False, {"error": f"HTTP {response.status_code}"}
    except requests.RequestException as e:
        return False, {"error": str(e)}
