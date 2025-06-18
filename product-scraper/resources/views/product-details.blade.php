<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <div class="card shadow rounded">
        <div class="row g-0">
            <div class="col-md-4">
                <img src="{{ $image }}" class="img-fluid rounded-start" alt="Product Image">
            </div>
            <div class="col-md-8">
                <div class="card-body">
                    <h4 class="card-title">{{ $title }}</h4>
                    <h5 class="text-success">{{ $price }}</h5>
                    <p class="card-text">{{ $description }}</p>

                    <a href="{{ route('product.scrape') }}" class="btn btn-primary mt-3">Scrape Again</a>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
