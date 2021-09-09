# Movie Website

This is a project I made to be able to track movies my friends/family and I have watched from the IMDB top 250 rated movies. 

It consists of three python scripts, a mySQL database and a website using HTML, CSS/Bootstrap, JS and PHP. 

## Scripts

The three scripts in python each have a purpose, but to go through them all you need to do is run imdbScrape.py since it uses the other two.

  1. The first one scrapes information from IMDB's website. (~~This'll break soon since they are updating their website.~~)  - UPDATED
    
  2. The second one uploads this information to the database.
    
  3. The third one uses the justwatch API from here: [GitHub](https://github.com/dawoudt/JustWatchAPI) to search for the movies and parses the query to then upload the information on whether movies can be streamed legally somewhere on to the database. 

## Database

The mySQL database is hard to upload here, but it has 6 tables

    movies | users | watchlist | followlist | streamable | streamsites

It's a very simple relational database. You can figure out the attributes yourself. 

## Website

The website uses PHP to access the database to extract and insert information. 

For the design of the website I just used a simple bootstrap nav bar. 

This information is then used on a website: [film.familjenfredriksson.se](https://film.familjenfredriksson.se)
