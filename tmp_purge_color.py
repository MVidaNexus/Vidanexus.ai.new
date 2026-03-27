import os
import re

directory = '/home/vidanexusai/public_html'
target_extensions = ['.css', '.js', '.php', '.blade.php']

replacements = [
    (r'#00f2ff', '#0ea5e9'),
    (r'rgba\(0,\s*242,\s*255,', 'rgba(14, 165, 233,'),
    (r'rgba\(0,\s*243,\s*255,', 'rgba(14, 165, 233,'),
    (r'rgb\(0,\s*242,\s*255\)', 'rgb(14, 165, 233)'),
    (r'rgb\(0,\s*243,\s*255\)', 'rgb(14, 165, 233)'),
]

match_count = 0
file_count = 0

for root, _, files in os.walk(directory):
    if 'storage/framework/views' in root or 'vendor' in root or 'node_modules' in root:
        continue
        
    for file in files:
        if any(file.endswith(ext) for ext in target_extensions):
            file_path = os.path.join(root, file)
            try:
                with open(file_path, 'r', encoding='utf-8') as f:
                    content = f.read()
                
                new_content = content
                for pattern, replacement in replacements:
                    new_content = re.sub(pattern, replacement, new_content, flags=re.IGNORECASE)
                
                if new_content != content:
                    with open(file_path, 'w', encoding='utf-8') as f:
                        f.write(new_content)
                    file_count += 1
                    match_count += content.count('#00f2ff') + content.count('0, 242, 255') + content.count('0,242,255') + content.count('0, 243, 255') + content.count('0,243,255')
            except Exception as e:
                pass

print(f"Purged {match_count} instances of the ugly color across {file_count} files!")
