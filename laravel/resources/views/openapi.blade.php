<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<text style="white-space: pre-wrap;">
    "openapi": "3.0.0",
    "info": {
        "title": "Gra Miejska - dokumentacja OpenAPI",
        "contact": {
            "email": "dawid.bazylewicz@studenci.collegiumwitelona.pl"
        },
        "version": "1.0"
    },
    "servers": [
        {
            "url": "https://127.0.0.1",
            "description": "Localhost"
        }
    ],
    "paths": {
        "/api/game/get": {
            "post": {
                "tags": [
                    "Game"
                ],
                "description": "Wziecie aktualnej gry uzytkownika. Jesli jest juz rozwiazana, zwraca statystyki wyboru.",
                "operationId": "6fb75deb0a42f045cc5394caab7608a4",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Stats displayed"
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Puzzle not found"
                    }
                }
            }
        },
        "/api/game/guess": {
            "post": {
                "tags": [
                    "Game"
                ],
                "description": "Przeslanie odpowiedzi uzytkownika na jego aktualna zagadke.",
                "operationId": "18a18661e5319fa7adfcd5909aa76eae",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token",
                                    "xvalue",
                                    "yvalue",
                                    "time"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Guess saved"
                    },
                    "400": {
                        "description": "Bad Request"
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Data not found"
                    }
                }
            }
        },
        "/api/gamesettings/update": {
            "post": {
                "tags": [
                    "Game Settings"
                ],
                "description": "Aktualizowanie ustawien gry. Tylko dla administratora. Opcjonalne parametry: timereset, mindistance, maxdistance, pointstoqualify, leaderboarddays.",
                "operationId": "b3b355149c00a3006fb0c47944f63173",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Settings saved"
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        },
        "/api/gamesettings/get": {
            "post": {
                "tags": [
                    "Game Settings"
                ],
                "description": "Wziecie najnowszych ustawien gry. Tylko dla administratora.",
                "operationId": "90314f05efd66545da136a5b959dc199",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Data fetched"
                    },
                    "400": {
                        "description": "Bad Request"
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        },
        "/api/guesses/stats": {
            "post": {
                "tags": [
                    "Guesses"
                ],
                "description": "Zdobadz statystyki zdjecia. Tylko dla administratora.",
                "operationId": "dacff2f87e4f8c86da30969bdf8dbc05",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token",
                                    "id"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Stats fetched"
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Data not found"
                    }
                }
            }
        },
        "/api/guesses/scoreboard": {
            "post": {
                "tags": [
                    "Guesses"
                ],
                "description": "Wez tabele wynikow.",
                "operationId": "73af5cfb9e66c01399c1de204701fe23",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Scoreboard fetched"
                    }
                }
            }
        },
        "/api/puzzles/index": {
            "post": {
                "tags": [
                    "Puzzles"
                ],
                "description": "Wylistowanie wszystkich zagadek. Tylko dla administratora.",
                "operationId": "b75337fa86de7123e7bd79b9813ad27c",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Retrieve successful"
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        },
        "/api/puzzles/create": {
            "post": {
                "tags": [
                    "Puzzles"
                ],
                "description": "Tworzenie zagadki. Tylko dla administratora.",
                "operationId": "8914402fe29bfd77bb79f9416b086f56",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token",
                                    "imagesource",
                                    "xvalue",
                                    "yvalue",
                                    "description",
                                    "difficulty"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Puzzle created"
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "No image source"
                    }
                }
            }
        },
        "/api/puzzles/delete": {
            "post": {
                "tags": [
                    "Puzzles"
                ],
                "description": "Usuniecie zagadki. Tylko dla administratora.",
                "operationId": "5aa46b1483266aad999e4bdf1fdd19af",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token",
                                    "puzzleid"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Puzzle deleted"
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Puzzle not found"
                    }
                }
            }
        },
        "/api/puzzles/edit": {
            "post": {
                "tags": [
                    "Puzzles"
                ],
                "description": "Edytowanie zagadki. Tylko dla administratora. Parametry xvalue, yvalue, description i difficulty sa opcjonalne.",
                "operationId": "d61efce9586a3da953aab60623a0d521",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token",
                                    "puzzleid"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Modify successful"
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "404": {
                        "description": "Puzzle not found"
                    }
                }
            }
        },
        "/api/users/index": {
            "post": {
                "tags": [
                    "Users"
                ],
                "description": "Wylistuj wszystkich uzytkownikow. Wymaga uprawnien administratora.",
                "operationId": "01b1d8acb3b6cbec82cdcf50999c2ed8",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Retrieve successful"
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        },
        "/api/users/register": {
            "post": {
                "tags": [
                    "Users"
                ],
                "description": "Rejestracja uzytkownika w bazie danych.",
                "operationId": "758e750cf3b7f1b6a9c906c443a12b83",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "name",
                                    "email",
                                    "password"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Registration successful"
                    },
                    "400": {
                        "description": "Bad Request"
                    }
                }
            }
        },
        "/api/users/google/redirect": {
            "get": {
                "tags": [
                    "Users"
                ],
                "description": "Przejscie na strone autoryzacji google.",
                "operationId": "25fe9fc581374551f7f3366cef110766",
                "requestBody": {
                    "content": {
                        "json": {}
                    }
                },
                "responses": {
                    "200": {
                        "description": "Redirecting"
                    }
                }
            }
        },
        "/api/users/facebook/redirect": {
            "get": {
                "tags": [
                    "Users"
                ],
                "description": "Przejscie na strone autoryzacji facebook.",
                "operationId": "2c0b05fd8a9827f69b094a0ae0158e52",
                "requestBody": {
                    "content": {
                        "json": {}
                    }
                },
                "responses": {
                    "200": {
                        "description": "Redirecting"
                    }
                }
            }
        },
        "/api/users/google/callback": {
            "get": {
                "tags": [
                    "Users"
                ],
                "description": "Powrot z autoryzacji google, rejestracja i logowanie.",
                "operationId": "b5521e3837ae9e8a60491d1017ed5af3",
                "requestBody": {
                    "content": {
                        "json": {}
                    }
                },
                "responses": {
                    "200": {
                        "description": "Login successful"
                    }
                }
            }
        },
        "/api/users/facebook/callback": {
            "get": {
                "tags": [
                    "Users"
                ],
                "description": "Powrot z autoryzacji facebook, rejestracja i logowanie.",
                "operationId": "68f50f1e9ca6d20a68df27a2216611a9",
                "requestBody": {
                    "content": {
                        "json": {}
                    }
                },
                "responses": {
                    "200": {
                        "description": "Login successful"
                    }
                }
            }
        },
        "/api/users/login": {
            "post": {
                "tags": [
                    "Users"
                ],
                "description": "Logowanie przez baze danych.",
                "operationId": "c9b20e2431404b7383ce7e626b4b6f05",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "email",
                                    "password"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Login successful"
                    },
                    "401": {
                        "description": "Unauthorized"
                    },
                    "403": {
                        "description": "Forbidden"
                    }
                }
            }
        },
        "/api/users/modify": {
            "post": {
                "tags": [
                    "Users"
                ],
                "description": "Zmiana nicku, zdjecia profilowego i hasla uzytkownika wysylajacego request. Parametry name, pfpnum i password sa opcjonalne.",
                "operationId": "064589f28b9d17a2d9f871d5929fa680",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Modify successful"
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        },
        "/api/users/adminmodify": {
            "post": {
                "tags": [
                    "Users"
                ],
                "description": "Zmiana aktualnej zagadki, roli administratora i banowanie uzytkownikow. Parametry currentgame, isadmin i isbanned sa opcjonalne. Tylko dla administratora.",
                "operationId": "7c3655cb24fe9ea72e63f96bed0eb230",
                "requestBody": {
                    "content": {
                        "json": {
                            "schema": {
                                "required": [
                                    "access_token",
                                    "userid"
                                ]
                            }
                        }
                    }
                },
                "responses": {
                    "200": {
                        "description": "Modify successful"
                    },
                    "401": {
                        "description": "Unauthorized"
                    }
                }
            }
        }
    },
    "tags": [
        {
            "name": "Game",
            "description": "Game"
        },
        {
            "name": "Game Settings",
            "description": "Game Settings"
        },
        {
            "name": "Guesses",
            "description": "Guesses"
        },
        {
            "name": "Puzzles",
            "description": "Puzzles"
        },
        {
            "name": "Users",
            "description": "Users"
        }
    ]
}
</text>
</html>
