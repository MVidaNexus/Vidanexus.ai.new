import urllib.request
import urllib.parse
import json

def fetch_explore(time_range="now 1-H", geo="EG"):
    req_data = f'{{"comparisonItem":[{{"keyword":"","geo":"{geo}","time":"{time_range}"}}],"category":0,"property":""}}'
    encoded_req = urllib.parse.quote(req_data)
    url = f"https://trends.google.com/trends/api/explore?hl=en-US&tz=-120&req={encoded_req}"
    
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        html = urllib.request.urlopen(req).read().decode('utf-8')
        print(html[:500])
        # Strip )]}',
        if html.startswith(")]}',"):
            html = html[5:]
        data = json.loads(html)
        print("Widgets:", data.get('widgets', []))
    except Exception as e:
        print("Error:", e)

fetch_explore()
