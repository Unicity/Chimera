## Internationalizacion.sqlite changes

`Internationalizacion.sqlite` is a sqlite DB that contains the countries and it's relative states/provinces. We don't have this information on UnityDB, because the idea behind  is to have sort of a stateless DB/tables, and by tracking the whole DB on the repository is a way to know, for a given moment, which version was available, and if it contains any kind of error.

However, by only having this `.sqlite` file makes really hard to work on it, and to be able to find in a fast way when an error was introduced (in case we find an error)
That is why we're starting to keep track of the queries that modifies the data contained in this DB.
For now, the model proposed is to have them separated by market, and within, to use the date where the change was made, followed by the ticket code, but this convention can be changed
