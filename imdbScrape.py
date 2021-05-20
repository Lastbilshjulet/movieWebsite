#!/usr/bin/python3

# -*- coding; utf-8 -*-



import json

import ssl

from urllib.request import Request, urlopen

from bs4 import BeautifulSoup



import uploadtoDB

import getStreamble



# For ignoring SSL certificate errors

ctx = ssl.create_default_context()

ctx.check_hostname = False

ctx.verify_mode = ssl.CERT_NONE



# Urls that need to be scraped

top_movies_url = "https://www.imdb.com/chart/top/"


# Fetching the BeautifulSoup object of an HTML page by passing its URL

def get_web_page_content(url):

    # Making the website believe that you are accessing it using a mozilla browser

    req = Request(url, headers={'User-Agent': 'Mozilla/5.0', 'Accept-Language': 'en-US'})

    webpage = urlopen(req).read()



    # Creating a BeautifulSoup object of the html page for easy extraction of data.

    soup = BeautifulSoup(webpage, 'html.parser')

    return soup



def get_minutes(hour_minutes):

    if hour_minutes.find("h") == -1:

        return hour_minutes.strip("min ")

    elif hour_minutes.find("min") == -1:

        hours = hour_minutes.strip("h ")

        return int(hours) * 60

    else:

        duration = hour_minutes.split()

        hours = duration[0].strip("h ")

        minutes = duration[1].strip("min ")

        return str((int(hours) * 60) + int(minutes))



# Going into individual movie/tv show URL and extracting extra details

def get_extra_details(movie):

    print(movie["imdb_link"])

    inner_soup = get_web_page_content(movie["imdb_link"])

    title_wrapper = inner_soup.find('div', attrs={'class': 'title_bar_wrapper'})

    strong = title_wrapper.find('strong')

    movie["ratings"] = strong['title'].strip()

    time = title_wrapper.find('time')

    movie["duration"] = get_minutes(time.text.strip())

    div = inner_soup.find('div', attrs={'class': 'plot_summary'})

    div_summary = div.find('div', attrs={'class': 'summary_text'})

    movie["summary"] = div_summary.text.strip()



    characters_div = div.findAll('div', attrs={'class': 'credit_summary_item'})

    character_data = characters_div[0].text.strip().split("\n")

    movie["director"] = character_data[1].replace("|", "").strip()

    character_data = characters_div[1].text.strip().split("\n")

    movie["writers"] = character_data[1].replace("|", "").strip()

    character_data = characters_div[2].text.strip().split("\n")

    movie["stars"] = character_data[1].replace("|", "").strip()

    return movie



# Fetching details of n number of movie/tv shows from the IMDB lists

def get_top_rated_imdb_hits(url, file_name, nos):

    print("------" + url + "------")

    soup = get_web_page_content(url)

    divs = soup.find('tbody', attrs={'class': 'lister-list'})

    movies = []



    i = 1

    for tr in divs.findAll('tr'):

        movie = {}

        td = tr.find('td', attrs={'class': 'titleColumn'})

        a = td.find('a')

        ref = a["href"].split("/")

        movie["id"] = ref[2][2:]

        ref = "/" + ref[1] + "/" + ref[2]

        movie["imdb_link"] = "https://www.imdb.com" + ref

        movie_data = td.text.strip().split("\n")

        movie["rank"], movie["name"], movie["year"] = movie_data[0].strip(

        '.'), movie_data[1].strip(), movie_data[2].strip()[1:5]

        print(movie["rank"], movie["name"])

        movie = get_extra_details(movie)

        movies.append(movie)

        i += 1

        if i > nos:

            break



    # Creates a json file with all the information that you extracted

    #with open(file_name, 'w') as outfile:

    #    json.dump(movies, outfile, indent=4)



    print("----------- scraped movie list -----------")

    print("-------- uploading new list to db --------")

    uploadtoDB.upload_to_db(movies)

    getStreamble.uploadNewStreamables()



get_top_rated_imdb_hits(top_movies_url, 'movies.json', 250)

print('--------- Extraction of data is complete. Check JSON file. ---------')

