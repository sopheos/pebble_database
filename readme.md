# Pebble/Database

`Pebble\Database` est un ensemble d'outils pour faciliter la manipulation de données avec une BDD SQL.

* `Pebble\Database\DriverInterface` : Connexion à la BDD et execution des requètes.
* `Pebble\Database\StatementInterface` : Représentation des requète préparés et du jeu de résultat associé.
* `Pebble\Database\Factory` : Cette classe est une simple fabrique pour créer un objet \PDO.

## Erreurs

Les erreurs déclanchent des exceptions `Pebble\Database\Exception`.

### Codes d'erreurs

* `CONNECT = 1`: Erreurs de connexion à la BDD.
* `PREPARE = 2` : Erreur lors de la création d'une requète préparée.
* `BIND = 3` : Erreur lors de l'association de valeurs aux paramètres.
* `EXECUTE = 4`: Erreur lors de l'execution d'une requète.
* `TRANSACTION = 5` : Erreur lors d'une transaction.

### Raccourcis

* `Exception::connect(string $message)`
* `Exception::prepare(string $message)`
* `Exception::bind(string $message)`
* `Exception::execute(string $message)`
* `Exception::transaction(string $message)`

## Query

La classe `Pebble\Database\Query` est utilisée pour stocker des requètes SQL et ses données.

Cette classe implémente l'interface `Pebble\Database\QueryInterface`.

* `__construct(string $statement = '', array $data = [])` : constructeur
* `setStatement(string $statement = '') : Query` : Modifie la requète SQL
* `setData(array $data = []) : Query` : Modifie les données de la requète.
* `getStatement() : string` : Retourne la requète SQL
* `getData() : array` Retourne un tableau des données à utiliser lors de l'éxecution de la requète.

## StatementInterface

L'interface `Pebble\Database\StatementInterface`est utilisée pour décrire l'accès aux résultats d'une requète SQL.

Méthodes :

* `bind(... $args) : StatementInterface` : Lie des données à une requàte préparée.
* `execute(array $data = []) : StatementInterface` : Execute une requète préparée.
* `count()` : Retourne le nombre de résultats
* `next($object = true)` : Retourne le résultat suivant
* `all(bool|string $object = true)` : Retourne les 0-n résultats de la requète dans un tableau
* `one(bool|string $object = true)` : Retourne un résultat si la réquète à exactement un résultat. Renvoit null sinon

Certaines requètes peuvent retourner des résultats mixtes :

* sous formes de tableau associatif (`$object === false`).
* d'objet `\stdClass` (`$object === true`).
* ou d'objet personnalisé (`$object === $classname`).

## DriverInterface

L'interface `Pebble\Database\DriverInterface`est utilisée pour décrire les interactions avec la base de donnée, et préparer les requètes.

* `use(string $database)` : Change la base de donnée courante.
* `getId() : int` : Retourne le dernier identifiant inseré (auto incrément). 
* `escape(string $str) : string` Échappe une  valeur.
* `transaction() : DriverInterface` : Démarre une transaction.
* `commit() : DriverInterface` : Valide une transaction.
* `rollback() : DriverInterface` : Annule une transaction.
* `query(string $sql) : StatementInterface` : Éxecute une requète préparée à partir d'une chaîne.
* `prepare(string $statement) : StatementInterface` : Prepares une requète préparée à partir d'une chaîne.
* `exec(QueryInterface $query) : StatementInterface` : Éxecute une requète à partir d'un objet qui implémente une `QueryInterface`.

## Query builder : QB

Le query builder `Pebble\Database\QB` est une classe utilisable en standalone qui permet de construire des requètes SQL.

Il faut utiliser construire là requète, la personnaliser puis faire le rendu d'objets `Pebble\Database\Query`.

````php
$qb = new QB('ma_table')
$qb->whereEq('id', 1);
$query = $qb->read();
````

### Construction

* `__construct(string $table = '')`
* `static create(string $table = '') : QB`

Le nom de la table est facultatif car certaines requètes n'agissent tout simplement pas sur une table.

### Personnalisation

Ces méthodes sont chaînables.

#### Selections

* `select(string ...$cols) : QB` : Choix des colonnes. Par défaut, toutes les colonnes sont retournées.
* `distinct() : QB` : Sélectionne uniquement les colonnes de valeurs distincts.

#### Tables

* `from(string $table)` : Attribution, modification de la table.
* `join(string $table, string $cond)` : Jointure interne.
* `left(string $stable, string $cond)` : Jointure externe gauche.
* `right(string $stable, string $cond)` : Jointure externe droite.

#### Conditions

##### Opérateurs *ET* (`AND`)

* `where(string $statement, ...$values) : QB` : Ajouter une condition à la requète.
* `whereNull(string $field) : QB` : La valeur de la colonne doit être nulle.
* `whereNotNull(string $field) : QB` : La valeur de la colonne ne doit pas être nulle.
* `whereEq(string $field, $value)` : La valeur de la colonne doit égaler la valeur demandée.
* `whereNot(string $field, $value)` : La valeur de la colonne doir diffèrer de la valeur demandée.
* `whereSup(string $field, $value) : QB` : La valeur de la colonne doit être supérieure à la valeur demandée.
* `whereInf(string $field, $value) : QB` : La valeur de la colonne doit être inférieure à la valeur demandée.
* `whereSupEq(string $field, $value) : QB` : La valeur de la colonne doit être supérieure ou égale à la valeur demandée.
* `whereInfEq(string $field, $value) : QB` : La valeur de la colonne doit être inférieure ou égale à la valeur demandée.
* `whereIn(string $col, array $values) : QB` : La valeur de la colonne doit correspondre à une des valeurs demandées.
* `whereNotIn(string $col, array $values) : QB` : La valeur de la colonne ne doit pas correspondre à une des valeurs demandées.
* `like(string $field, $value) : QB` : La valeur de la colonne doit correspondre à la au modèle demandée.
* `notLike(string $field, $value) : QB` : La valeur de la colonne ne doit pas correspondre à la au modèle demandée.

##### Opérateurs *OU* (`OR`)

* `orWhere(string $statement, ...$values) : QB` : Ajouter une condition à la requète.
* `orWhereNull(string $field) : QB` : La valeur de la colonne doit être nulle.
* `orWhereNotNull(string $field) : QB` : La valeur de la colonne ne doit pas être nulle.
* `orWhereEq(string $field, $value)` : La valeur de la colonne doit égaler la valeur demandée.
* `orWhereNot(string $field, $value)` : La valeur de la colonne doir diffèrer de la valeur demandée.
* `orWhereSup(string $field, $value) : QB` : La valeur de la colonne doit être supérieure à la valeur demandée.
* `orWhereInf(string $field, $value) : QB` : La valeur de la colonne doit être inférieure à la valeur demandée.
* `orWhereSupEq(string $field, $value) : QB` : La valeur de la colonne doit être supérieure ou égale à la valeur demandée.
* `orWhereInfEq(string $field, $value) : QB` : La valeur de la colonne doit être inférieure ou égale à la valeur demandée.
* `orWhereIn(string $col, array $values) : QB` : La valeur de la colonne doit correspondre à une des valeurs demandées.
* `orWhereNotIn(string $col, array $values) : QB` : La valeur de la colonne ne doit pas correspondre à une des valeurs demandées.
* `orLike(string $field, $value) : QB` : La valeur de la colonne doit correspondre à la au modèle demandée.
* `orNotLike(string $field, $value) : QB` : La valeur de la colonne ne doit pas correspondre à la au modèle demandée.

##### Blocs de conditions

* `groupStart() : QB` : Débute un bloc (opérateur *ET*)
* `orGroupStart() : QB` : Débute un bloc (opérateur *OU*)
* `groupEnd() : QB` : Termine un bloc (opérateur *ET/OU*)

#### Groupes

* `groupBy(string ...$cols) : QB` : Groupe les resultats par colonnes.
* `having(string $statement, ...$values) : QB` : Ajoute une condition pour les fonctions statistiques (opérateur *ET*)
* `orHaving(string $statement, ...$values) : QB` : Ajoute une condition pour les fonctions statistiques (opérateur *OR*)

#### Tris

* `orderBy(string ...$statements) : QB` : Tri (ascendant ou descendant).
* `orderAsc(string ...$col) : QB` : Tri ascendant.
* `orderDesc(string ...$col) : QB` : Tri descendant.

Exemples :

````php
$qb->orderBy('etat', 'dat_creatione_creation desc');
$qb->orderAsc('etat')->orderDesc('date_creation');
````

#### Limites

* `limit(int $limit, int $offset = 0) : QB` : Limite le nombre des résultats

#### Ajout de données

Ces méthodes sont utiles pour insérer, remplacer, modifier des données en BDD.

* `add(string $col, $value) : QB` : Ajoute une donnée. Les données sont protégées. 
* `addList(array $data) : QB` : Ajoute une liste de données. Les données sont protégées.
* `addRaw(string $col, $value) : QB` : Ajoute une donnée. Les données ne sont pas protégées / échapées. 
* `addListRaw(array $data) : QB` : Ajoute une liste de données. Les données ne sont pas protégées / échapées.
* `increment(string $col, $val = 1) : QB` : Incrémente une colonne.
* `decrement(string $col, $val = 1) : QB` : Décrémente une colonne.

### Rendu

* `read() : Query` : Crée une requète de selection de données.
* `count() : Query` : Crée une requète pour compter le nombre de données.
* `insert() : Query` : Crée une requète pour ajouter des données.
* `replace() : Query` : Crée une requète pour remplacer des données.
* `update() : Query` : Crée une requète pour modifier des données.
* `delete() : Query` : Crée une requète pour supprimer des données.

### Insertions / Remplacements multiples

* `insertAll(array $data) : Query`
* `replaceAll(array $data) : Query`

## Sessions : SessionHandler

La classe `Pebble\Database\SessionHandler` permet de gérer les sessions en base de données. Cette classe implémente `\SessionHandlerInterface` et une instance de celle ci peut être utilisé avec la fonction native `session_set_save_handler`.

* Le premier paramètre du constructeur doit être un objet qui implémente `Pebble\Database\DriverInterface` 
* Le second paramètre facultatif permet de configurer le nom de la table et le nom des champs de la base de données :
    * `table` : nom de la table (`sessions` par défaut)
    * `id` : nom de l'identifiant (`id` par défaut)
    * `time` : nom du champ pour l'expiration (`ts` par défaut)
    * `data` : nom du champ pour stocker les données (`data` par défaut)

Configuration SQL idéale :

````sql
CREATE TABLE `sessions` (
`id` char(40) NOT NULL,
`ts` int(10) NOT NULL DEFAULT 0,
`data` blob NOT NULL,
PRIMARY KEY (`id`),
KEY `session_ts_idx` (`ts`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
````


## Manipuler les tables : DBForge

La classe DBForge permet de manipuler la structure d'une base ou d'une table.
Cette classe génère des requêtes (objet `Query`),
donc elle n'execute pas les requètes automatiquement.

### Créer un objet DBForge

````php
$forge = DBForge::create()
````

### Créer une base

`createSchema(string $name, string $charset = 'utf8mb4', string $collate = 'utf8mb4_general_ci'): string`

* `name` : Nom de la base de donnée.
* `charset` : Jeux de caractères utilisé.
* `collate` : Collation de la base.

Exemple :

    $db->query(
        DBForge::create()->createSchema('donnons')
    );

### Supprimer une base

`dropSchema(string $name): string`

* `name` : Nom de la base de donnée.

### Créer une table

`createTable(string $name, string $comment = ""): string`

Les colonnes, clés et index doivent être déclarées préalablement.

* `name` : Nom de la table.
* `comment` : Description de la table.

### Supprimer une table

`dropTable(string $name): string`

* `name` : Nom de la table.

### Renommer une table

`renameTable(string $from, string $to): string`

* `from` : Nom de la table
* `to` : Nouveau nom de la table

### Modifier une table

`alterTable(string $name)`

* `name` : Nom de la table.

Les manipulation de colonnes, clés et index doivent être déclarées préalablement.

*Rappel SQL : Manipuler les clés étrangères et les colonnes dans une même requète provoque souvent des erreurs SQL.
Pensez à séparer les requètes en différentes étapes.*

### Ajouter d'une colonne

`addColumn(string $name, ?callable $callback = null) : DBForge`

* `name` Nom de la colonne
* `callback` Fonction de rappel pour configurer les propriétés de la colonne.

### Supprimer un colonne

`dropColumn(string $name) : DBForge`

* `name` Nom de la colonne.

### Modifier une colonne

`changeColumn(string $name, ?string $new_name = null, ?callable $callback = null) : DBForge`

* `name` Nom de la colonne
* `new_name` Si fournit, nouveau nom de la colonne
* `callback` Fonction de rappel pour configurer les propriétés de la colonne.

### Configurer les propriétés d'une colonne

La configuration des propriétés se font dans la fonction de rappel des méthodes `addColumn` et `changeColumn`

Exemple :

````php
function(Column $column) {
    $column->char(40);
}
````

#### `type(string $type, ...$constraints) : Column`

* `type` Type du champ
* `contraints` Contraintes

#### `bool(bool $default = false) : Column`

Faux type : tinyint(1) unsigned not null avec une valeur par défaut.

* `default` : Valeur par défaut : si vrai, 1 sinon 0

#### `int($prefix = '') : Column`

Type INT

* `prefix` : TINY SMALL MEDIUM BIG.

#### `float() : Column`

Type FLOAT

#### `decimal(int $len = 10, int $precision = 2) : Column`

Type DECIMAL

* `precision` Nombre de chiffres significatifs total
* `scale` Nombre de chiffres pour la partie décimale du nombre.

#### `char(int $len) : Column`

Type CHAR

* `len` Nombre de caractères réservés

#### `varchar(int $len = 255) : Column`

Type VARCHAR

* `len` Nombre de caractères

#### `text($prefix = '') : Column`

Type TEXT

* `prefix` TINY, MEDIUM, LONG

#### `blob($prefix = '') : Column`

Type BLOB

* `prefix` TINY, MEDIUM, LONG

#### `timestamp() : Column`

Type TIMESTAMP

#### `datetime() : Column`

Type DATETIME

#### `date() : Column`

Type DATE

#### `time() : Column`

Type TIME

#### `unsigned(bool $value = true) : Column`

Pour les champs de type nombre. Ajoute l'option UNSIGNED.

#### `notNull() : Column`

Le champ ne doit pas être null.

#### `defaultValue($value, bool $quote = true) : Column`

Valeur par défaut. Active notNull().

* `value` Valeur
* `quote` Échappe ou non la valeur.

#### `defaultTimestamp($on_update = false) : Column`

Valeur par défaut : CURRENT_TIMESTAMP

* `on_update` Si vrai, ajoute ON UPDATE CURRENT_TIMESTAMP

#### `autoIncrement() : Column`

Pour les champs entiers, ajoute l'autoincrément.

#### `comment(string $value) : Column`

Ajoute un commentaire.

#### `first() : Column`

Positionne le champ en première position (ALTER TABLE)

#### `after(string $name): Column`

Positionne le champ après le champ `name` (ALTER TABLE)`

### Manipulation d'une clé primaire

* `addPrimary(... $names) : DBForge` : Ajoute une(des) colonne(s) à la clé primaire.
* `dropPrimary() : DBForge` : Supprimer la clé primaire.

### Manipulation d'un index

#### `addIndex(string $name, array $cols = []) : DBForge`

Ajoute un index au format `{table}_{name}_idx ('name' ASC)`.

Le nom de la table et le suffix est ajouté automatiquement.

Pour personnaliser la liste des colonnes, il faut passer un tableau associatif :
    * clé : nom de la colonne
    * valeur : ASC si true sinon DESC

#### `addUnique(string $name, array $cols = []) : DBForge`

Ajoute un index unique au format `{table}_{name}_unq ('name' ASC)`.

Le nom de la table et le suffix est ajouté automatiquement.

Pour personnaliser la liste des colonnes, il faut passer un tableau associatif :
    * clé : nom de la colonne
    * valeur : ASC si true sinon DESC

#### `dropIndex(string $name) : DBForge`

Supprime un index. Le nom de la table et le suffix est ajouté automatiquement.

#### `dropUnique(string $name) : DBForge`

Supprime un index unique. Le nom de la table et le suffix est ajouté automatiquement.

### Manipulation d'une clé étrangère

#### `addFk(string $field, string $target, $delete = 'CASCADE', $update = 'CASCADE') : DBForge`

Ajoute une clé étrangère.

* `field` : nom de la colonne
* `target` : colonne cible au format `TABLE.COLUMN`
* `delete` : option pour la suppression
* `update` : option pour la modification

#### `addFkIndex(string $field)` : DBForge

* Ajoute un index pour la clé étrangère.

#### `dropFk(string $field) : DBForge`

Supprime une clé étrangère.

#### `dropFkIndex(string $field) : DBForge`

* Supprime un index pour la clé étrangère.

### Exemples

#### Créer une table

````php
DBForge::create()
    ->addColumn('id', function(Column $col) {
        $col->type('int', 11)->unsigned()->autoIncrement();
    })
    ->addColumn('name', function(Column $col) {
        $col->type('varchar', 50)->notNull();
    })
    ->addColumn('captain', function(Column $col) {
        $col->->type('int', 11)->unsigned();
    })
    ->addColumn('assistant'function(Column $col) {
        $col->->type('int', 11)->unsigned();
    })
    ->addPrimary('id')
    ->addFk('captain', 'players.id')
    ->addFk('assistant', 'players.id')
    ->createTable('tests.teams')
````

### Modifier une table

````php
DBForge::create()
    ->changeColumn('id', function(Column $col) {
    $col->type('int', 11)->unsigned()->autoIncrement();
    })
    ->addPrimary('id')
    ->alterTable('tests.teams')
````
