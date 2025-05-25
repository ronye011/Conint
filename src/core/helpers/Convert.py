import sys
import io
import tempfile
import os
import pandas as pd
import camelot

# Lê o PDF da entrada padrão
pdf_data = sys.stdin.buffer.read()

with tempfile.NamedTemporaryFile(delete=False, suffix=".pdf") as temp_pdf:
    temp_pdf.write(pdf_data)
    temp_pdf_path = temp_pdf.name

with tempfile.NamedTemporaryFile(delete=False, suffix=".xlsx") as temp_xlsx:
    excel_path = temp_xlsx.name

try:
    # Usa Camelot no modo stream para melhor leitura de PDFs sem bordas
    tables = camelot.read_pdf(temp_pdf_path, pages='all', flavor='stream')

    # Combina todas as tabelas
    combined_df = pd.concat([table.df for table in tables], ignore_index=True)

    # Salva como XLSX
    combined_df.to_excel(excel_path, index=False)

    with open(excel_path, 'rb') as f:
        sys.stdout.buffer.write(f.read())

except Exception as e:
    print(f"Erro ao processar o PDF: {e}", file=sys.stderr)

finally:
    os.remove(temp_pdf_path)
    if os.path.exists(excel_path):
        os.remove(excel_path)
