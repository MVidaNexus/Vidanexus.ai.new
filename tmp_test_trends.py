import urllib.request
import urllib.parse
import json

def fetch_explore(time_range="now 4-H", geo="EG"):
    # First get the token
    url = f"https://trends.google.com/trends/api/explore?hl=en-US&tz=-120&req=%7B%22comparisonItem%22:%5B%7B%22keyword%22:%22%22,%22geo%22:%22{geo}%22,%22time%22:%22{time_range}%22%7D%5D,%22category%22:0,%22property%22:%22%22%7D"
    req = urllib.request.Request(url, headers={'User-Agent': 'Mozilla/5.0'})
    try:
        html = urllib.request.urlopen(req).read().decode('utf-8')
        print(html[:500])
    except Exception as e:
        print("Error:", e)

fetch_explore()
