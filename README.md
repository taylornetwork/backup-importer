# Backup Importer

This package will allow you to import data to your database from a backup database that is not identical. 

This is useful if you re-write an application and the database structure changes.

## Install

Using Composer

```bash
$ composer require taylornetwork/backup-importer
```

### Publish Config

```bash
$ php artisan vendor:publish
```

### Add Backup Database Config

By default this will use your `mysql` database connection with the `DB_BACKUP_DATABASE` value from your `.env` file or `backup` as the database name

Add connection details to `config/backup-importer.php`

## Usage

### Run your importers

```bash
$ php artisan importer:run
```


### Create an Importer

```bash
$ php artisan importer:new CustomerImporter
```

Would generate an importer `App\Backup\Importers\CustomerImporter.php` by default.

### Simple Importer

```php
use TaylorNetwork\BackupImporter\BaseImporter;

class CustomerImporter extends BaseImporter
{
    public function import(): int
    {
        return $this->simpleImport();
    }
}
``` 

By default the importer assumes the following
- There is a model to import to
- The model being imported is `App\Customer` (see Advanced Config to change)
- The backup table name is `customers`
- The backup table fields are all the same as the model's fields

### Advanced Importers

You can override the above assumptions on an importer by importer basis

#### Override Model

Add a protected `$model` variable in your importer

```php
protected $model = App\Models\Customer::class;
```

#### Override the Backup Table Name

Add a protected `$backupTableName` variable in your importer

```php
protected $backupTableName = 'xyz_customers';
```

#### Ignore Model (For a Pivot Table)

If you don't have a model for this importer set a protected `$ignoreModel` to `true`

```php
protected $ignoreModel = true;
```

#### Override Columns From Backup Table

You can override the columns that are taken from the backup table, or rename them.

Add a public `getColumnMap()` function that returns the array of columns to get.

```php
public function getColumnMap(): array
{
    return [
        'firstname as first_name,
        'lastname as last_name',
        'address',
    ];
}
```

#### Example

```php
use TaylorNetwork\BackupImporter\BaseImporter;

class CustomerImporter extends BaseImporter
{
    /**
     * Set the model to import to
     */
    protected $model = App\Models\Customer::class;
    
    /**
     * Set the backup table name
     */
    protected $backupTableName = 'xyz_customers';
    
    /**
     * Set the columns to get from the backup table
     */
    public function getColumnMap(): array
    {
        return [
            'firstname as first_name',
            'lastname as last_name',
            'address',
        ];
    }

    
    public function import(): int
    {
       return $this->simpleImport();    
    }
}
``` 

### Customizing the `import()` function

The `import()` function by default will return `$this->simpleImport()` which is fine for simple tables with no relations, however you will likely want to customize the import logic.

#### Notes

- Access the model by `$this->getModel()`
- Access the database query data by `$this->items()`
- Access the fluent builder for more complex queries by `$this->builder()`
- Access the fluent builder AFTER the select call by `$this->select()`
- Whenever you import a row you should call `$this->increment()` to add to the total of rows imported
- If you use `$this->increment()` your return statement should be `$this->getImportTotal()`

#### Example

Let's say you have an application that has customers and services. Each customer can have many services with properties.
You have the following models which you store in `app/`

- `App\Customer`
- `App\Service`
- `App\CustomerService`

For the customer and service models, you used the simple import method.

```php
// App\Backup\Importers\CustomerServiceImporter.php

use TaylorNetwork\BackupImporter\BaseImporter;
use App\Customer;

class CustomerServiceImporter extends BaseImporter
{
    public function getColumnMap(): array
    {
        return [
            'customer_id',
            'service_id',
            'qty',
            'description',
            'last_service_date',
        ];
    }
    
    public function import(): int
    {
        $rows = $this->select()->where('last_service_date', '!=', null)->get();
        
        foreach($rows as $row) {
            Customer::find($row->customer_id)->services()->create([
                'service_id' => $row->service_id,
                'qty' => $row->qty,
                'desc' => $row->description,
                'last_date' => $row->last_service_date,
            ]);
            
            $this->increment();
        }
        
        return $this->getImportTotal();
    }
}
```
