
##SpeedTest

Test was made on Author table with fields id, first_name, last_name and email.
Fields first_name, last_name and email are encrypted.
The table was 33097 lines.

With no encryption:
- insert (1000 rows) **546ms**
- findByFirstName (10 rows) **37ms**
- paginate (page 20 with 50 items and orderedByFirstName) **31ms**

With encryption with mysql_encryption behavior
- insert (1000 rows) **584ms**
- findByFirstName (10 rows) **128ms**
- paginate (page 20 with 50 items and orderedByFirstName) **139ms**

With encryption with uwdoem/encryption behavior
- insert (1000 rows) **577ms**
- findByFirstName (10 rows) **not available**
- paginate (page 20 with 50 items and orderedByFirstName) **not available**