import requests
from xml.etree import ElementTree
from reportlab.lib.pagesizes import letter
from reportlab.pdfgen import canvas
from reportlab.lib.colors import blue, black
import re
from textwrap import wrap

def search_arxiv(keyword, max_results=8):
    base_url = "http://export.arxiv.org/api/query"
    params = {
        "search_query": f"all:{keyword}",
        "start": 0,
        "max_results": max_results,
        "sortBy": "lastUpdatedDate",
        "sortOrder": "descending",
    }
    response = requests.get(base_url, params=params)
    if response.status_code == 200:
        root = ElementTree.fromstring(response.content)
        papers = []
        for entry in root.findall("{http://www.w3.org/2005/Atom}entry"):
            title = entry.find("{http://www.w3.org/2005/Atom}title").text
            summary = entry.find("{http://www.w3.org/2005/Atom}summary").text
            link = entry.find("{http://www.w3.org/2005/Atom}id").text
            updated = entry.find("{http://www.w3.org/2005/Atom}updated").text
            authors = [
                author.find("{http://www.w3.org/2005/Atom}name").text
                for author in entry.findall("{http://www.w3.org/2005/Atom}author")
            ]
            date, time = updated.split("T")
            time = time.replace("Z", "")
            if keyword.lower() in title.lower() or keyword.lower() in summary.lower():
                papers.append({
                    "title": title.strip() if title else "No title available",
                    "summary": summary.strip() if summary else "No summary available",
                    "link": link.strip(),
                    "date": date,
                    "time": time,
                    "authors": authors,
                })
        return papers
    else:
        print(f"Error: {response.status_code}")
        return []

def add_clickable_links(text):
    url_pattern = re.compile(r'https?://\S+')
    urls = re.findall(url_pattern, text)
    for url in urls:
        text = text.replace(url, f'Click here for more ({url})')
    return text

def check_space(c, y_position, line_height, bottom_margin, height):
    if y_position < bottom_margin + line_height:
        c.showPage()
        y_position = height - 72
    return y_position

def generate_pdf(papers, keyword, filename="research_papers.pdf"):
    c = canvas.Canvas(filename, pagesize=letter)
    width, height = letter
    left_margin = 72
    right_margin = width - left_margin
    top_margin = 72
    bottom_margin = 72
    line_height = 16
    y_position = height - top_margin
    c.setFont("Helvetica-Bold", 18)
    title = f"Research Papers on '{keyword}'"
    wrapped_title = wrap(str(title), width=70)
    for line in wrapped_title:
        y_position = check_space(c, y_position, line_height, bottom_margin, height)
        c.drawCentredString(width / 2, y_position, line)
        y_position -= line_height
    y_position -= 20
    external_links = {
        "Google Scholar": f"https://scholar.google.com/scholar?q={keyword}",
        "Semantic Scholar": f"https://www.semanticscholar.org/search?q={keyword}",
        "IEEE Xplore": f"https://ieeexplore.ieee.org/search/searchresult.jsp?newsearch=true&queryText={keyword}",
        "PubMed": f"https://pubmed.ncbi.nlm.nih.gov/?term={keyword}"
    }
    c.setFont("Helvetica", 10)
    c.setFillColor(blue)
    for platform, url in external_links.items():
        c.drawString(left_margin, y_position, platform)
        c.linkURL(url, (left_margin, y_position - 2, right_margin, y_position + 10), relative=1)
        y_position -= line_height
    y_position -= line_height
    c.setFillColor(black)
    for idx, paper in enumerate(papers, 1):
        y_position = check_space(c, y_position, line_height, bottom_margin, height)
        c.setFont("Helvetica-Bold", 14)
        c.drawString(left_margin, y_position, f"Paper {idx}:")
        y_position -= line_height
        c.setFont("Helvetica-Bold", 12)
        title_lines = wrap(str(paper["title"]), width=60)
        for line in title_lines:
            y_position = check_space(c, y_position, line_height, bottom_margin, height)
            c.drawString(left_margin, y_position, line)
            y_position -= line_height
        c.setFont("Helvetica", 10)
        c.drawString(left_margin, y_position, f"Date: {paper['date']}")
        y_position -= line_height
        c.drawString(left_margin, y_position, f"Time: {paper['time']}")
        y_position -= line_height
        if paper["authors"]:
            authors_text = ", ".join(paper["authors"][:10])
            author_lines = wrap(authors_text, width=70)
            c.drawString(left_margin, y_position, "Authors:")
            y_position -= line_height
            for line in author_lines:
                y_position = check_space(c, y_position, line_height, bottom_margin, height)
                c.drawString(left_margin + 20, y_position, line)
                y_position -= line_height
        else:
            c.drawString(left_margin, y_position, "Authors: Not available")
            y_position -= line_height
        c.setFont("Helvetica", 12)
        c.drawString(left_margin, y_position, "Summary:")
        y_position -= line_height
        summary_lines = wrap(str(paper["summary"]), width=70)
        if summary_lines:
            c.drawString(left_margin + 20, y_position, f"- {summary_lines[0]}")
            y_position -= line_height
            for line in summary_lines[1:]:
                y_position = check_space(c, y_position, line_height, bottom_margin, height)
                c.drawString(left_margin + 20, y_position, line)
                y_position -= line_height
        c.setFont("Helvetica", 10)
        c.setFillColor(blue)
        c.drawString(left_margin, y_position, "Click here for more")
        c.linkURL(paper['link'], (left_margin, y_position - 2, right_margin, y_position + 10), relative=1)
        c.setFillColor(black)
        y_position -= line_height * 2
    c.save()

if __name__ == "__main__":
    user_keyword = input("Enter a keyword to search for research papers: ")
    results = search_arxiv(user_keyword, max_results=100)
    if results:
        print("\nFetched Research Papers:\n")
        for idx, paper in enumerate(results, 1):
            print(f"Paper {idx}: {paper['title']}")
            print(f"Date: {paper['date']}")
            print(f"Time: {paper['time']}")
            print(f"Authors: {', '.join(paper['authors']) if paper['authors'] else 'Not available'}")
            print(f"Summary: {paper['summary']}")
            print(f"Link: {paper['link']}")
            print("-" * 80)
        generate_pdf(results, user_keyword)
        print(f"\nPDF Generated named 'research_papers.pdf' on the keyword '{user_keyword}'")
        print("The Top 100 research papers are saved in the PDF file.")
    else:
        print(f"No papers found for keyword: {user_keyword}")
