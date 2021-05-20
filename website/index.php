<!doctype html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>FF movies</title>

    <link rel="shortcut icon" type="image/ico" href="media/favicon.ico"/>
    <link rel="stylesheet" href="stylesheet.css">

    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-eOJMYsd53ii+scO/bJGFsiCZc+5NDVN2yr8+0RDqr0Ql0h+rP48ckxlpbzKgwra6" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.12.1/css/all.min.css">
</head>

<body class="d-flex flex-column h-100">

    <header>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark" aria-label="Eighth navbar example">
            <div class="container">
                <a class="navbar-brand" href="index.php">FF movies</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                    data-bs-target="#navbarsExample07" aria-controls="navbarsExample07" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarsExample07">
                    <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="movies.php">Movie list</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="whowatched.php">Who watched</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="latest.php">Latest watched</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="stats.php">Stats</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="follow.php">Follow</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <!-- Begin page content -->
    <main class="flex-shrink-0">
        <div class="container">
            <h1 class="mt-5">Welcome to this website!</h1>
            <p class="lead">There is not much here now, only the top 250 movie list.</p>
            <ul>
                <li class="nav-item">
                    <a class="dropdown-item" href="movies.php">Movie list</a>
                </li>
                <li class="nav-item">
                    <a class="dropdown-item" href="whowatched.php">Who watched</a>
                </li>
                <li class="nav-item">
                    <a class="dropdown-item" href="latest.php">Latest watched</a>
                </li>
                <li class="nav-item">
                    <a class="dropdown-item" href="stats.php">Stats</a>
                </li>
                <li class="nav-item">
                    <a class="dropdown-item" href="follow.php">Follow</a>
                </li>
            </ul>
        </div>
    </main>

    <footer class="footer mt-auto py-3 bg-light">
        <div class="container">
            <h6 class="text-center text-muted pt-2"><i class="far fa-copyright"></i> 2021</h6>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-JEW9xMcG8R+pH31jmWH6WWP0WintQrMb4s7ZOdauHnUtxwoG2vI5DkLtS3qm9Ekf"
        crossorigin="anonymous"></script>


</body>

</html>