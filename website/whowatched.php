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
                            <a class="nav-link active" href="whowatched.php">Who watched</a>
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
    <main class="container p-0">
        <div id="stats">
            <h2 id="title" class="m-4">See what other users have watched</h2>
            <div id="followPrompt" class="m-4">
                <form class="col-sm-4 col-12" role="form" method="GET" action="<?php echo $_SERVER["PHP_SELF"]; ?>">
                    <?php
                        $sql = "SELECT userID, userName
                        FROM user
                        WHERE userID IN (SELECT followlist.following 
                                        FROM followlist 
                                        INNER JOIN user 
                                        ON user.userID = followlist.user
                                        WHERE user.userName = \"".$_COOKIE["usrnm"]."\")
                        ORDER BY userName";
                        $sql_ = mysqli_query($conn, $sql);
                        if (mysqli_num_rows($sql_) == 0)
                        {
                            echo "<h4>You don't follow anyone, go to <a href='follow.php'>follow</a>.</h4>";
                        }
                        else
                        {
                            $userID = "";
                            echo "<select class='form-select' name='user'>";
                            if (strlen($_SERVER["REQUEST_URI"]) > 16)
                            {
                                $userID = substr($_SERVER["REQUEST_URI"], 0, strpos($_SERVER["REQUEST_URI"], "&submit"));
                                $userID = substr($userID, -3);
                                $sql_userName = "SELECT userName FROM user WHERE userID = ".$userID;
                                $sql_userName_ = mysqli_query($conn, $sql_userName);
                                $rowUserName = mysqli_fetch_row($sql_userName_);
                                echo "<option value='".$userID."'>".$rowUserName[0]."</option>";
                            }
                            while ($row = mysqli_fetch_assoc($sql_))
                            {
                                if ($row["userID"] != $userID)
                                    echo "<option value='".$row["userID"]."'>".$row["userName"]."</option>";
                            }
                            echo "</select>";
                            echo "<input id='submitWho' type='submit' value='See movies' name='submitWho' class='btn btn-primary m-3'/>";
                            echo "<input id='submitDifference' type='submit' value='See difference' name='submitDifference' class='btn btn-primary'/>";
                        }
                    ?>
                </form>
                <?php
                    if (isset($_GET["submitWho"]))
                    {
                        $sql_getMovies = "SELECT * 
                        FROM movie 
                        INNER JOIN watchlist
                        ON watchlist.movieID = movie.movieID
                        WHERE watchlist.userID = ".$_GET["user"]."
                        ORDER BY movieRank";
                        $movies = mysqli_query($conn, $sql_getMovies);
                        if (mysqli_num_rows($movies) > 0)
                        {
                            while ($row = mysqli_fetch_assoc($movies))
                            {
                                $duration = $row['movieDuration'];
                                $minutes = $duration % 60;
                                $hours = ($duration - $minutes)/60;
                                $rating = $row['movieRatings'];
                                $shortRatings = substr($rating, 0, strpos($rating, " b"));
                                $rating = substr($rating, 0, strpos($rating, " rat"));
                                echo "<div class='m-0'>";
                                echo "<h5 id='movie".$row['movieRank']."' class='m-0 p-3 d-block text-truncate pointer'>".$row['movieRank'].". ".$row['movieName'];
                                echo "<span class='rating d-none d-md-inline-block'><span class='m-2'>".$shortRatings."</span></span><i class='fas fa-chevron-down'></i></h5>";
                                echo "<div id='movieInfo".$row['movieRank']."' class='mx-2' style='display: none;'>";
                                echo "<h6 class='dividers mt-2'><span class='d-sm-inline-block d-md-none'>Year: </span>".$row['movieYear']."<br class='d-sm-inline-block d-md-none'>";
                                echo "<b class='dividers d-none d-md-inline-block'>|</b><span class='d-sm-inline-block d-md-none'></span>Duration: ".$hours."h ".$minutes."min<br class='d-sm-inline-block d-md-none'>";
                                echo "<b class='dividers d-none d-md-inline-block'>|</b><span class='d-sm-inline-block d-md-none'></span>Rating: ".$rating."s<br class='d-sm-inline-block d-md-none'></h6><hr>";
                                echo "<div class='m-3'>";
                                echo "<h6><b>Director:</b> ".$row['movieDirector']."</h6>";
                                echo "<h6><b>Writers:</b> ".$row['movieWriters']."</h6>";
                                echo "<h6><b>Stars:</b> ".$row['movieStars']."</h6>";
                                echo "<h6><b>Summary:</b> ".$row['movieSummary']."</h6>";
                                echo "<h6><b>IMDB-link:</b> <a target='_blank' href='".$row['movieLink']."'>".$row['movieLink']."</a></h6>";
                                $sql_getStreamble = "SELECT streamsite.streamsiteName, streamable.streamLink
                                FROM streamsite
                                INNER JOIN streamable
                                ON streamsite.streamsiteID = streamable.streamsiteID
                                INNER JOIN movie
                                ON movie.movieID = streamable.movieID
                                WHERE movie.movieID = ".$row['movieID'];
                                $sql_getStreamsites = mysqli_query($conn, $sql_getStreamble);
                                if (mysqli_num_rows($sql_getStreamsites) > 0)
                                {
                                    echo "<h6><b>Streamable: </b>";
                                    $counter = 0;
                                    while ($rowStreamlist = mysqli_fetch_assoc($sql_getStreamsites))
                                    {
                                        if ($counter != 0)
                                        {
                                            echo ", ";
                                        }
                                        echo "<a class='link-dark' href='".$rowStreamlist["streamLink"]."' target='_blank'>".$rowStreamlist["streamsiteName"]."</a>";
                                        $counter++;
                                    }
                                    echo "</h6>";
                                }
                                echo "</div></div></div><hr class='m-0'>";
                            }
                        }
                        else
                        {
                            $sql = "SELECT userName FROM user WHERE userID = ".$_GET["user"];
                            $user = mysqli_query($conn, $sql);
                            $userName = mysqli_fetch_row($user);
                            echo "<h5>".$userName[0]." has not seen any movies.</h5>";
                        }
                    }

                    if (isset($_GET["submitDifference"]))
                    {
                        $sql_getMovies = "SELECT * 
                        FROM movie 
                        INNER JOIN watchlist
                        ON watchlist.movieID = movie.movieID
                        WHERE watchlist.userID = ".$_GET["user"]." AND movie.movieID NOT IN (SELECT movie.movieID
                                                                                            FROM movie
                                                                                            INNER JOIN watchlist
                                                                                            ON watchlist.movieID = movie.movieID
                                                                                            INNER JOIN user
                                                                                            ON user.userID = watchlist.userID
                                                                                            WHERE user.userName = \"".$_COOKIE["usrnm"]."\")
                        ORDER BY movieRank";
                        $movies = mysqli_query($conn, $sql_getMovies);
                        if (mysqli_num_rows($movies) > 0)
                        {
                            while ($row = mysqli_fetch_assoc($movies))
                            {
                                $duration = $row['movieDuration'];
                                $minutes = $duration % 60;
                                $hours = ($duration - $minutes)/60;
                                $rating = $row['movieRatings'];
                                $shortRatings = substr($rating, 0, strpos($rating, " b"));
                                $rating = substr($rating, 0, strpos($rating, " rat"));
                                echo "<div class='m-0'>";
                                echo "<h5 id='movie".$row['movieRank']."' class='m-0 p-3 d-block text-truncate pointer'>".$row['movieRank'].". ".$row['movieName'];
                                echo "<span class='rating d-none d-md-inline-block'><span class='m-2'>".$shortRatings."</span></span><i class='fas fa-chevron-down'></i></h5>";
                                echo "<div id='movieInfo".$row['movieRank']."' class='mx-2' style='display: none;'>";
                                echo "<h6 class='dividers mt-2'><span class='d-sm-inline-block d-md-none'>Year: </span>".$row['movieYear']."<br class='d-sm-inline-block d-md-none'>";
                                echo "<b class='dividers d-none d-md-inline-block'>|</b><span class='d-sm-inline-block d-md-none'></span>Duration: ".$hours."h ".$minutes."min<br class='d-sm-inline-block d-md-none'>";
                                echo "<b class='dividers d-none d-md-inline-block'>|</b><span class='d-sm-inline-block d-md-none'></span>Rating: ".$rating."s<br class='d-sm-inline-block d-md-none'></h6><hr>";
                                echo "<div class='m-3'>";
                                echo "<h6><b>Director:</b> ".$row['movieDirector']."</h6>";
                                echo "<h6><b>Writers:</b> ".$row['movieWriters']."</h6>";
                                echo "<h6><b>Stars:</b> ".$row['movieStars']."</h6>";
                                echo "<h6><b>Summary:</b> ".$row['movieSummary']."</h6>";
                                echo "<h6><b>IMDB-link:</b> <a target='_blank' href='".$row['movieLink']."'>".$row['movieLink']."</a></h6>";
                                $sql_getStreamble = "SELECT streamsite.streamsiteName, streamable.streamLink
                                FROM streamsite
                                INNER JOIN streamable
                                ON streamsite.streamsiteID = streamable.streamsiteID
                                INNER JOIN movie
                                ON movie.movieID = streamable.movieID
                                WHERE movie.movieID = ".$row['movieID'];
                                $sql_getStreamsites = mysqli_query($conn, $sql_getStreamble);
                                if (mysqli_num_rows($sql_getStreamsites) > 0)
                                {
                                    echo "<h6><b>Streamable: </b>";
                                    $counter = 0;
                                    while ($rowStreamlist = mysqli_fetch_assoc($sql_getStreamsites))
                                    {
                                        if ($counter != 0)
                                        {
                                            echo ", ";
                                        }
                                        echo "<a class='link-dark' href='".$rowStreamlist["streamLink"]."' target='_blank'>".$rowStreamlist["streamsiteName"]."</a>";
                                        $counter++;
                                    }
                                    echo "</h6>";
                                }
                                echo "</div></div></div><hr class='m-0'>";
                            }
                        }
                        else
                        {
                            $sql = "SELECT userName FROM user WHERE userID = ".$_GET["user"];
                            $user = mysqli_query($conn, $sql);
                            $userName = mysqli_fetch_row($user);
                            echo "<h5>You've seen all movies ".$userName[0]." has seen.</h5>";
                        }
                    }
                ?>
            </div>
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

    <script>
        window.addEventListener("load", () => {
            for (let i = 1; i < 251; i++)
            {
                console.log(i);
                try {
                    document.getElementById("movie" + i).addEventListener("click", () => {
                        let movieInfo = document.getElementById("movieInfo" + i);
                        if (movieInfo.style.display === "none") {
                            document.getElementById("movie" + i).classList.remove("text-truncate");
                            movieInfo.style.display = ""
                        } else {
                            document.getElementById("movie" + i).classList.add("text-truncate");
                            movieInfo.style.display = "none"
                        }
                    });
                } catch (err) {
                    console.log(err);
                }
            }
        });
    </script>

</body>

</html>
