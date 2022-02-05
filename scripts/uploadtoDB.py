#!/usr/bin/python3

import json
import mysql.connector as mysql
from mysql.connector import Error

def fetchDbConfig():
    login = []
    with open('.dbconfig', 'r') as infile:
        for i, line in enumerate(infile):
            login.append(line.replace("\n", ""))
        infile.close()
    return login

def execute_query(db_connection, db_cursor, query, val):
    try:
        db_cursor.execute(query, val)
        db_connection.commit()
    except:
        print("could not commit query")

def update_ratings_db(movieRatings, movieID):
    query = "UPDATE movie SET movieRatings = %s WHERE movieID = %s"
    val = (movieRatings, movieID)
    return query, val
    
def update_rank_db(movieRank, movieID):
    query = "UPDATE movie SET movieRank = %s WHERE movieID = %s"
    val = (movieRank, movieID)
    return query, val

def insert_db(x):
    attr = "movieID, movieLink, movieRank, movieName, movieYear, movieRatings, movieDuration, movieSummary, movieDirector, movieWriters, movieStars"
    query = "INSERT INTO movie (" + attr + ") VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)"
    val = (x["id"], x["imdb_link"], x["rank"], x["name"], x["year"], x["ratings"], x["duration"], x["summary"], x["director"], x["writers"], x["stars"])
    return query, val

def upload_to_db(movies):
    if not movies:
        print("No movies passed, checking local json file...")
        try:
            with open('movies.json', 'r') as infile:
                movies = list(json.load(infile))
        except:
            print("movies.json could not be opened.")
            exit()
    
    login = fetchDbConfig()

    db_connection = None
    try:
        db_connection = mysql.connect(host=login[0], database=login[1], user=login[2], passwd=login[3])
        db_cursor = db_connection.cursor()
    except:
        print("Could not connect to the database.")
        exit()

    movie = []

    for x in movies:
        movie.append(int(x["id"]))
        try:
            query = "SELECT movieRank FROM movie WHERE movieID = " + x["id"]
            db_cursor.execute(query)
            data = list(sum(db_cursor.fetchall(), ()))
            if data == []:
                print(x["id"], "inserting", x["name"], "into db...")
                query, val = insert_db(x)
                execute_query(db_connection, db_cursor, query, val)
            elif str(data[0]) != x["rank"]:
                print(data[0], "updating db for", x["name"], "...")
                query, val = update_rank_db(x["rank"], x["id"])
                execute_query(db_connection, db_cursor, query, val)
            else:
                print(data[0], "no need for update or insert", x["id"])
            query, val = update_ratings_db(x["ratings"], x["id"])
            execute_query(db_connection, db_cursor, query, val)
        except Error as error:
            print("could not commit query")
            print(error)
            print("exiting...")
            exit()

    query = "SELECT movieID FROM movie"
    db_cursor.execute(query)
    data = list(sum(db_cursor.fetchall(), ()))

    for x in data:
        if x not in movie:
            query, val = update_rank_db('NULL', x)
            execute_query(db_connection, db_cursor, query, val)

    try:
        db_cursor.close()
        db_connection.close()
        print("closed connections...")
    except:
        print("could not close connections")
