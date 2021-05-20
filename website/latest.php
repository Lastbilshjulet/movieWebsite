<?php
    include_once 'connection.php';
?>

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
                            <a class="nav-link" href="index.php">Home</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="movies.php">Movie list</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="whowatched.php">Who watched</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="latest.php">Latest watched</a>
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
    <main class="container p-0">
        <div id="latestwatched">
            <?php
                $sql = "SELECT DISTINCT user.userName
                , movie.movieRank
                , movie.movieName
                , movie.movieLink
                , watchlist.watchDate
                FROM movie
                INNER JOIN watchlist
                ON watchlist.movieID = movie.movieID
                INNER JOIN user
                ON user.userID = watchlist.userID
                WHERE user.userID IN   (SELECT followlist.following 
                                        FROM followlist 
                                        INNER JOIN user 
                                        ON user.userID = followlist.user
                                        WHERE user.userName = \"".$_COOKIE["usrnm"]."\")
                ORDER BY watchlist.watchDate DESC
                LIMIT 10";
                $sql_ = mysqli_query($conn, $sql);
                $counter = 1;
                if (mysqli_num_rows($sql_) > 0)
                {
                    echo "<h2 id='title' class='m-4'>10 Latest Watched Movies</h2>";
                    echo "<div class='mx-3'>";
                    while ($row = mysqli_fetch_array($sql_))
                    {
                        echo "<h5><b>{$counter}. ".$row["userName"]."</b></h5>";
                        echo "<div class='mx-3'><h5><b>".$row["movieRank"].".</b> <a class='text-dark' target='_blank' href='".$row['movieLink']."'>".$row['movieName']."</a></h5>";
                        echo "<h6>".$row["watchDate"]."</h6></div>";
                        $counter++;
                    }
                    echo "</div>";
                }
                else
                {
                    echo "<h2 id='title' class='m-4'>You don't follow anyone, go to <a href='follow.php'>follow</a></h2>";
                }
            ?>
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
