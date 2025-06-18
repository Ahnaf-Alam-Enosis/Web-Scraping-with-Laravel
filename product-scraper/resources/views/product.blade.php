<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Website Scraper</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

  <div class="container py-5">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card shadow rounded-4">
          <div class="card-body p-4">
            <h3 class="card-title mb-4 text-center">Website Scraper</h3>
            <form id="scrapeForm" method="GET" action="{{ route('scrape') }}">
            @csrf
              <div class="mb-3">
                <label for="urlInput" name="url" class="form-label">Enter Website URL</label>
                <input type="url" class="form-control" name="url" id="urlInput" placeholder="https://example.com" required>
              </div>
              <div class="d-grid">
                <button type="submit" class="btn btn-primary">Scrape</button>
              </div>
            </form>
            <div id="result" class="mt-4"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
