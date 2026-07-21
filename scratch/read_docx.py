import zipfile
import xml.etree.ElementTree as ET
import os
import re

def get_docx_text(path):
    doc = zipfile.ZipFile(path)
    xml_content = doc.read('word/document.xml')
    root = ET.fromstring(xml_content)
    
    # Extract paragraphs and tables
    text = []
    # Namespaces
    namespaces = {'w': 'http://schemas.openxmlformats.org/wordprocessingml/2006/main'}
    
    # We can iterate through the body elements to get text in order
    body = root.find('w:body', namespaces)
    if body is not None:
        for elem in body:
            # Paragraphs
            if elem.tag.endswith('p'):
                p_text = ''.join(node.text for node in elem.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t') if node.text)
                text.append(p_text)
            # Tables
            elif elem.tag.endswith('tbl'):
                for row in elem.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}tr'):
                    row_text = []
                    for cell in row.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}tc'):
                        cell_text = ''.join(node.text for node in cell.iter('{http://schemas.openxmlformats.org/wordprocessingml/2006/main}t') if node.text)
                        row_text.append(cell_text)
                    text.append(" | ".join(row_text))
    return '\n'.join(text)

docx_path = 'Dokumen LA/LA-Revisi1.docx'
if os.path.exists(docx_path):
    full_text = get_docx_text(docx_path)
    # Search for "BAB III" or "BAB 3" or "Use Case"
    print("Docx loaded successfully. Total characters:", len(full_text))
    
    # Write full text to a scratch file so we can view it
    with open('scratch/docx_extracted.txt', 'w', encoding='utf-8') as f:
        f.write(full_text)
    print("Full text extracted to scratch/docx_extracted.txt")
else:
    print(f"File not found: {docx_path}")
