from flask import Flask, render_template, request, send_file, jsonify, session
from flask_cors import CORS
import os
import sys
import secrets  # Add this import
from Final import search_arxiv, generate_pdf
from setup import setup_directories
from db import record_paper_generation

app = Flask(__name__)
app.secret_key = os.environ.get('FLASK_SECRET_KEY', secrets.token_hex(16))

CORS(app)
if not setup_directories():
    print("Failed to set up required directories!")
    sys.exit(1)

@app.route('/', methods=['GET'])
def home():
    return render_template('index.html')

@app.route('/generate', methods=['GET', 'POST'])
def generate():
    if request.method != 'POST':
        return jsonify({'error': 'Only POST method is allowed'}), 405
        
    try:
        topic = request.form.get('topic')
        max_results = min(int(request.form.get('papers', 8)), 100)
        
        print(f"Searching for: {topic}, Max Results: {max_results}")
        
        papers = search_arxiv(topic, max_results=max_results)
        
        if papers:
            pdf_filename = f"research_papers_{topic.replace(' ', '_')}.pdf"
            pdf_path = os.path.join(os.getcwd(), 'generated_pdfs', pdf_filename)
            
            # Ensure directory exists
            os.makedirs('generated_pdfs', exist_ok=True)
            
            generate_pdf(papers, topic, filename=pdf_path)
            
            # Record the paper generation without user association
            record_paper_generation(topic, pdf_filename, None)  # Pass None for user_id
            
            return send_file(
                pdf_path,
                as_attachment=True,
                download_name=pdf_filename,
                mimetype='application/pdf'
            )
        else:
            return jsonify({'error': 'No papers found'}), 404
            
    except Exception as e:
        print(f"Error: {str(e)}")
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    print("Starting Flask server...")
    print(f"Current working directory: {os.getcwd()}")
    print(f"Python version: {sys.version}")
    app.run(host='127.0.0.1', port=8000, debug=True) 