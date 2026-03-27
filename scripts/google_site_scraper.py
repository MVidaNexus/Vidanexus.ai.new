import sys
import json
import time
import random
import argparse
import requests
from bs4 import BeautifulSoup

try:
    from fake_useragent import UserAgent
    ua = UserAgent()
except ImportError:
    ua = None

def get_random_ua():
    if ua:
        return ua.random
    return random.choice([
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/121.0.0.0 Safari/537.36",
        "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Safari/605.1.15",
        "Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:122.0) Gecko/20100101 Firefox/122.0",
        "Mozilla/5.0 (iPhone; CPU iPhone OS 17_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.3 Mobile/15E148 Safari/604.1"
    ])

def scrape_google(domain, max_pages=3):
    results = []
    session = requests.Session()
    session.headers.update({
        'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
        'Accept-Language': 'ar,en-US;q=0.9,en;q=0.8',
        'Referer': 'https://www.google.com/'
    })

    for page in range(max_pages):
        start = page * 10
        url = f"https://www.google.com/search?q=site:{domain}&start={start}&hl=ar"
        session.headers.update({'User-Agent': get_random_ua()})
        
        try:
            response = session.get(url, timeout=10)
            if response.status_code == 429:
                print("DEBUG: 429 Too Many Requests")
                break # Rate limited
                
            soup = BeautifulSoup(response.text, 'html.parser')
            print(f"DEBUG: Status {response.status_code}, Title: {soup.title.text if soup.title else 'No Title'}")
            
            # Google SERP structure
            blocks = soup.find_all('div', class_='g')
            
            # Fallback for mobile/different layouts
            if not blocks:
                blocks = soup.find_all('div', class_='ezO2ce')
                
            for block in blocks:
                title_elem = block.find('h3')
                link_elem = block.find('a', href=True)
                snippet_elem = block.find('div', class_='VwiC3b') or block.find('span', class_='aCOpRe')
                
                title = title_elem.text if title_elem else ''
                link = link_elem['href'] if link_elem else ''
                snippet = snippet_elem.text if snippet_elem else ''
                
                if title and link:
                    # Clean title
                    if ' - ' in title:
                        title = title.rsplit(' - ', 1)[0]
                    elif ' | ' in title:
                        title = title.rsplit(' | ', 1)[0]
                        
                    results.append({
                        'title': title.strip(),
                        'url': link,
                        'snippet': snippet.strip()
                    })
                    
            time.sleep(random.uniform(1.2, 2.5))
            
        except Exception as e:
            pass

    return results

if __name__ == "__main__":
    parser = argparse.ArgumentParser(description='Scrape Google for site keywords')
    parser.add_argument('-d', '--domain', required=True, help='Domain to scrape')
    parser.add_argument('--pages', type=int, default=3, help='Max pages to scrape')
    
    args = parser.parse_args()
    domain = args.domain.replace('https://', '').replace('http://', '').replace('www.', '')
    if '/' in domain:
        domain = domain.split('/')[0]
        
    data = scrape_google(domain, args.pages)
    
    # Analyze the results
    output = {
        "domain": domain,
        "total_results": len(data),
        "pages": data
    }
    
    print(json.dumps(output, ensure_ascii=False))
