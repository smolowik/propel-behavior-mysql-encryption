##mysql_encryption behavior for propel 2.x
This behavior is written for propel 2.x and it uses mysql functions AES_ENCRYPT and AES_DECRYPT for encryption.

## Instalation

```
composer require smolowik/propel-behavior-mysql-encryption
```

##How to use

First you need to define the encryption key:
```php
Smolowik\Propel\Passphrase::createInstance("YOUR_SECRET_KEY");
```
Then, add the behavior to the table:
```xml
<table name="author" phpName="Author">
    <column name="id" type="integer" required="true" primaryKey="true" autoIncrement="true"/>
    <column name="first_name" type="varchar" size="50" required="true"/>
    <column name="last_name" type="varchar" size="255" required="true"/>
    <column name="email" type="varchar" size="100" required="true"/>
    <behavior name="mysql_encryption">
        <parameter name="columns" value="first_name,last_name,email" />
    </behavior>
</table>
```
In this part you define which columns should be encrypted. In our example, this columns: first_name, last_name and email.
```xml
<parameter name="columns" value="first_name,last_name,email" />
```
After building the model and update the database schema, you should be able to use encrypted columns as with any other in the table.
I use this behavior to getting the list (paginate) with searching and sorting. 
I use it also to read and write individual rows from the database. 
If you want to use it in a more sophisticated way, you should thoroughly test it yourself.


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