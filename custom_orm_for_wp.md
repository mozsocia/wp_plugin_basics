## deepseek_non_reasoning model ans

### 🔴  is there any orm for wordpress?

Of course! Yes, there are several excellent ORM (Object-Relational Mapper) solutions for WordPress. They provide a much more elegant, object-oriented way to interact with your database compared to using raw SQL or the basic `WP_Query`.

The most prominent and powerful one is **Eloquent**, which comes from the Laravel framework.

Here’s a breakdown of the main options:

---

### 1. Corcel (The Most Popular Choice)

**Corcel** is a package that allows you to use the Laravel **Eloquent ORM** directly within WordPress.

*   **How it works:** It uses your existing WordPress `wp_posts` table (and related tables) but lets you query them using Eloquent's beautiful and powerful syntax.
*   **Key Idea:** A WordPress post is treated as a base model. You can then create models for your post types (e.g., `Post`, `Page`, `Product`).

**Basic Example with Corcel:**

```php
// First, you'd install Corcel via Composer
// composer require corcel/corcel

use Corcel\Model\Post;

// Get all published posts
$posts = Post::published()->get();

foreach ($posts as $post) {
    echo $post->title; // Access properties like an object
    echo $post->meta->subtitle; // Access post meta easily
}

// Find a post by ID or slug
$post = Post::find(1);
$post = Post::slug('hello-world')->first();

// Create a custom query
$recentPosts = Post::type('post')
                   ->status('publish')
                   ->orderBy('post_date', 'desc')
                   ->take(5)
                   ->get();
```

**Pros:**
*   **Laravel's Eloquent:** You get the full power of one of the best ORMs available.
*   Very intuitive and fluent syntax.
*   Excellent for handling relationships between posts (e.g., post to author, post to categories, post to thumbnail).
*   Great for projects where WordPress is used as a headless CMS.

**Cons:**
*   Primarily designed for reading data from the standard WordPress tables. Writing/updating can be done but requires caution to not break WordPress core functionality.

---

### 2. WordPress's Own "Quasi-ORM": `WP_Query` and `WP_User_Query`

While not a true ORM, it's important to mention that WordPress has its own set of query classes that abstract SQL away. They are more of a **Query Builder** pattern.

*   **`WP_Query`:** For querying posts and custom post types.
*   **`WP_User_Query`:** For querying users.
*   **`WP_Comment_Query`:** For querying comments.
*   **`WP_Term_Query`:** For querying categories and tags.

**Example with `WP_Query`:**

```php
$query = new WP_Query([
    'post_type' => 'post',
    'post_status' => 'publish',
    'posts_per_page' => 10,
    'meta_key' => 'featured',
    'meta_value' => 'yes'
]);

if ($query->have_posts()) {
    while ($query->have_posts()) {
        $query->the_post();
        // Use standard template tags
        the_title();
    }
}
wp_reset_postdata();
```

**Pros:**
*   Deeply integrated and guaranteed to be compatible with WordPress.
*   Handles complex query parameters well.
*   The "WordPress way" of doing things.

**Cons:**
*   Not object-oriented. It returns `WP_Post` objects or arrays, which are more limited.
*   The API is based on a large array of parameters, which can be less readable than a fluent interface.
*   Does not natively handle relationships well.

---

### 3. Custom ORM Implementation with Posts Table

For complex custom tables (not the standard `wp_posts`), you can build your own lightweight ORM. This often involves:

1.  Creating your own custom table.
2.  Writing a class that extends `WP_Query` or uses `$wpdb` with a more object-oriented wrapper.

This is more advanced and requires a good understanding of both OOP and the WordPress database class `$wpdb`.

**Example Skeleton:**

```php
class ProductModel {
    protected $table;

    public function __construct() {
        global $wpdb;
        $this->table = $wpdb->prefix . 'custom_products';
    }

    public function find($id) {
        global $wpdb;
        return $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$this->table} WHERE id = %d", $id)
        );
    }

    // ... other methods for all(), create(), update(), delete()
}
```

---

### Summary & Recommendation

| Solution | Best For | Type |
| :--- | :--- | :--- |
| **Corcel (Eloquent)** | **Modern PHP projects**, headless WordPress, developers familiar with Laravel. The best choice for a true ORM experience. | Full ORM |
| **`WP_Query` etc.** | **Traditional theme & plugin development** where you need 100% compatibility and are querying standard WordPress data. | Query Builder |
| **Custom `$wpdb` Wrapper** | **Highly custom plugins** that use their own database tables and need a simple, custom data abstraction layer. | Custom ORM |

**Conclusion:**

If you want a **real ORM** in WordPress, **Corcel is the definitive answer**. It brings the power and elegance of Laravel's Eloquent to the WordPress ecosystem.

If you are building a standard theme or plugin and only need to query core WordPress data (posts, users, etc.), sticking with the built-in **`WP_Query`** is the safest and most conventional path.


### 🔴 please teach me Custom ORM Implementation using $wpdb


I'll teach you how to build a custom ORM implementation using WordPress's `$wpdb`. This is perfect for when you need to work with custom tables beyond the standard WordPress posts structure.

## Basic ORM Structure

Let's start with a base model that all your custom models will extend:

### 1. Base Model Class

```php
<?php
/**
 * Custom ORM Base Model
 */
class BaseModel {
    
    protected $table;
    protected $primary_key = 'id';
    protected $fillable = [];
    protected $data = [];
    protected $exists = false;
    
    public function __construct($data = []) {
        global $wpdb;
        $this->table = $wpdb->prefix . $this->table;
        $this->fill($data);
    }
    
    /**
     * Fill model with data
     */
    public function fill($data) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (in_array($key, $this->fillable) || empty($this->fillable)) {
                    $this->data[$key] = $value;
                }
            }
        }
        return $this;
    }
    
    /**
     * Magic getter
     */
    public function __get($key) {
        if (array_key_exists($key, $this->data)) {
            return $this->data[$key];
        }
        return null;
    }
    
    /**
     * Magic setter
     */
    public function __set($key, $value) {
        if (empty($this->fillable) || in_array($key, $this->fillable)) {
            $this->data[$key] = $value;
        }
    }
    
    /**
     * Save model to database
     */
    public function save() {
        global $wpdb;
        
        $data = $this->data;
        
        if ($this->exists) {
            // Update existing record
            $primary_value = $data[$this->primary_key];
            unset($data[$this->primary_key]);
            
            $result = $wpdb->update(
                $this->table,
                $data,
                [$this->primary_key => $primary_value]
            );
            
            return $result !== false;
        } else {
            // Insert new record
            $result = $wpdb->insert($this->table, $data);
            
            if ($result) {
                $this->data[$this->primary_key] = $wpdb->insert_id;
                $this->exists = true;
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Delete record
     */
    public function delete() {
        global $wpdb;
        
        if (!$this->exists) {
            return false;
        }
        
        $result = $wpdb->delete(
            $this->table,
            [$this->primary_key => $this->data[$this->primary_key]]
        );
        
        if ($result) {
            $this->exists = false;
            return true;
        }
        
        return false;
    }
    
    /**
     * Find by primary key
     */
    public static function find($id) {
        global $wpdb;
        
        $model = new static();
        $table = $model->table;
        
        $result = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM {$table} WHERE {$model->primary_key} = %d", $id),
            ARRAY_A
        );
        
        if ($result) {
            $model->fill($result);
            $model->exists = true;
            return $model;
        }
        
        return null;
    }
    
    /**
     * Get all records
     */
    public static function all() {
        global $wpdb;
        
        $model = new static();
        $table = $model->table;
        
        $results = $wpdb->get_results("SELECT * FROM {$table}", ARRAY_A);
        
        $collection = [];
        foreach ($results as $result) {
            $item = new static($result);
            $item->exists = true;
            $collection[] = $item;
        }
        
        return $collection;
    }
    
    /**
     * Where clause
     */
    public static function where($column, $operator, $value = null) {
        global $wpdb;
        
        // If only two parameters passed, assume operator is '='
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $model = new static();
        return new QueryBuilder($model, $column, $operator, $value);
    }
    
    /**
     * Convert to array
     */
    public function toArray() {
        return $this->data;
    }
}
```

### 2. Query Builder Class

```php
<?php
/**
 * Query Builder for ORM
 */
class QueryBuilder {
    
    protected $model;
    protected $wheres = [];
    protected $order_by = '';
    protected $limit = '';
    protected $offset = '';
    
    public function __construct($model, $column = null, $operator = null, $value = null) {
        $this->model = $model;
        
        if ($column !== null) {
            $this->where($column, $operator, $value);
        }
    }
    
    /**
     * Add where condition
     */
    public function where($column, $operator, $value = null) {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        $this->wheres[] = [
            'column' => $column,
            'operator' => $operator,
            'value' => $value
        ];
        
        return $this;
    }
    
    /**
     * Add order by clause
     */
    public function orderBy($column, $direction = 'ASC') {
        $this->order_by = "ORDER BY {$column} {$direction}";
        return $this;
    }
    
    /**
     * Add limit clause
     */
    public function limit($limit) {
        $this->limit = "LIMIT {$limit}";
        return $this;
    }
    
    /**
     * Add offset clause
     */
    public function offset($offset) {
        $this->offset = "OFFSET {$offset}";
        return $this;
    }
    
    /**
     * Get results
     */
    public function get() {
        global $wpdb;
        
        $table = $this->model->table;
        $sql = "SELECT * FROM {$table}";
        
        // Add WHERE clauses
        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $where_conditions = [];
            
            foreach ($this->wheres as $where) {
                $where_conditions[] = $wpdb->prepare(
                    "{$where['column']} {$where['operator']} %s", 
                    $where['value']
                );
            }
            
            $sql .= implode(' AND ', $where_conditions);
        }
        
        // Add ORDER BY, LIMIT, OFFSET
        if (!empty($this->order_by)) {
            $sql .= " {$this->order_by}";
        }
        
        if (!empty($this->limit)) {
            $sql .= " {$this->limit}";
        }
        
        if (!empty($this->offset)) {
            $sql .= " {$this->offset}";
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        $collection = [];
        foreach ($results as $result) {
            $item = new $this->model($result);
            $item->exists = true;
            $collection[] = $item;
        }
        
        return $collection;
    }
    
    /**
     * Get first result
     */
    public function first() {
        $results = $this->limit(1)->get();
        return !empty($results) ? $results[0] : null;
    }
    
    /**
     * Get count
     */
    public function count() {
        global $wpdb;
        
        $table = $this->model->table;
        $sql = "SELECT COUNT(*) FROM {$table}";
        
        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $where_conditions = [];
            
            foreach ($this->wheres as $where) {
                $where_conditions[] = $wpdb->prepare(
                    "{$where['column']} {$where['operator']} %s", 
                    $where['value']
                );
            }
            
            $sql .= implode(' AND ', $where_conditions);
        }
        
        return (int) $wpdb->get_var($sql);
    }
}
```

### 3. Concrete Model Example

Let's create a specific model for a "products" table:

```php
<?php
/**
 * Product Model
 */
class Product extends BaseModel {
    
    protected $table = 'custom_products';
    protected $primary_key = 'id';
    protected $fillable = [
        'name', 
        'description', 
        'price', 
        'stock_quantity', 
        'is_active',
        'created_at'
    ];
    
    /**
     * Custom method to get active products
     */
    public static function getActive() {
        return self::where('is_active', 1)->orderBy('name', 'ASC')->get();
    }
    
    /**
     * Custom method to get expensive products
     */
    public static function getExpensive($min_price = 100) {
        return self::where('price', '>=', $min_price)
                  ->where('is_active', 1)
                  ->orderBy('price', 'DESC')
                  ->get();
    }
}
```

### 4. Database Table Creation

Here's how to create the custom table:

```php
<?php
/**
 * Create custom table on plugin activation
 */
function create_custom_tables() {
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'custom_products';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE {$table_name} (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        description text,
        price decimal(10,2) NOT NULL DEFAULT '0.00',
        stock_quantity int NOT NULL DEFAULT 0,
        is_active tinyint(1) NOT NULL DEFAULT 1,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) {$charset_collate};";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Register activation hook
register_activation_hook(__FILE__, 'create_custom_tables');
```

## Usage Examples

Now let's see how to use our custom ORM:

### Basic CRUD Operations

```php
<?php
// Create a new product
$product = new Product();
$product->name = 'Awesome Product';
$product->description = 'This is an amazing product';
$product->price = 99.99;
$product->stock_quantity = 50;
$product->is_active = 1;
$product->save();

// Find a product by ID
$product = Product::find(1);
if ($product) {
    echo $product->name;
    echo $product->price;
}

// Update a product
$product = Product::find(1);
if ($product) {
    $product->price = 89.99;
    $product->save();
}

// Delete a product
$product = Product::find(1);
if ($product) {
    $product->delete();
}

// Get all products
$products = Product::all();
foreach ($products as $product) {
    echo $product->name . ' - $' . $product->price . '<br>';
}
```

### Advanced Querying

```php
<?php
// Using where clauses
$expensive_products = Product::where('price', '>', 50)
                            ->where('is_active', 1)
                            ->orderBy('price', 'DESC')
                            ->get();

// Using custom methods
$active_products = Product::getActive();
$expensive_products = Product::getExpensive(100);

// Complex queries
$products = Product::where('stock_quantity', '>', 0)
                  ->where('price', '<=', 200)
                  ->orderBy('name', 'ASC')
                  ->limit(10)
                  ->offset(0)
                  ->get();

// Get count
$active_count = Product::where('is_active', 1)->count();
```

## Advanced Features You Can Add

### 1. Relationships

```php
<?php
// In your BaseModel or specific model
public function hasMany($related_model, $foreign_key, $local_key = null) {
    if ($local_key === null) {
        $local_key = $this->primary_key;
    }
    
    $related = new $related_model();
    return $related->where($foreign_key, $this->data[$local_key])->get();
}
```

### 2. Timestamps

```php
<?php
// Add to BaseModel
protected $timestamps = true;

public function save() {
    if ($this->timestamps) {
        $current_time = current_time('mysql');
        
        if (!$this->exists) {
            $this->data['created_at'] = $current_time;
        }
        $this->data['updated_at'] = $current_time;
    }
    
    return parent::save();
}
```

### 3. Validation

```php
<?php
// Add to BaseModel
protected $rules = [];

public function validate() {
    $errors = [];
    
    foreach ($this->rules as $field => $rule) {
        $value = $this->data[$field] ?? null;
        
        // Add validation logic here
        if ($rule === 'required' && empty($value)) {
            $errors[$field] = "{$field} is required";
        }
        // Add more validation rules as needed
    }
    
    return empty($errors) ? true : $errors;
}
```

This custom ORM gives you:
- **Object-oriented database interaction**
- **Fluent query builder interface**
- **Type safety with fillable fields**
- **Easy CRUD operations**
- **Extensible architecture**

You can extend this further based on your specific needs, adding features like soft deletes, relationships, pagination, and more advanced query methods.




### 🔴 please add feature relationships, this is most important for building a scallable plugin in wp


I'll add comprehensive relationship support to our custom ORM. This is crucial for building scalable WordPress plugins with complex data structures.

## Enhanced BaseModel with Relationships

### 1. Updated BaseModel with Relationship Methods

```php
<?php
/**
 * Enhanced Base Model with Relationship Support
 */
class BaseModel {
    
    protected $table;
    protected $primary_key = 'id';
    protected $fillable = [];
    protected $data = [];
    protected $exists = false;
    protected $with = []; // Eager loading relationships
    
    // Relationship arrays
    protected $has_many = [];
    protected $belongs_to = [];
    protected $has_one = [];
    protected $many_to_many = [];
    
    public function __construct($data = []) {
        global $wpdb;
        $this->table = $wpdb->prefix . $this->table;
        $this->fill($data);
    }
    
    /**
     * Fill model with data
     */
    public function fill($data) {
        if (!empty($data)) {
            foreach ($data as $key => $value) {
                if (in_array($key, $this->fillable) || empty($this->fillable)) {
                    $this->data[$key] = $value;
                }
            }
        }
        return $this;
    }
    
    /**
     * Set relationships to eager load
     */
    public static function with($relationships) {
        $model = new static();
        $model->with = is_array($relationships) ? $relationships : func_get_args();
        return new QueryBuilder($model);
    }
    
    /**
     * HAS ONE Relationship
     * Example: User has one Profile
     */
    public function hasOne($related, $foreign_key = null, $local_key = null) {
        global $wpdb;
        
        $related_model = new $related();
        $foreign_key = $foreign_key ?: $this->getForeignKey();
        $local_key = $local_key ?: $this->primary_key;
        
        return $related_model->where($foreign_key, $this->$local_key)->first();
    }
    
    /**
     * BELONGS TO Relationship
     * Example: Post belongs to User
     */
    public function belongsTo($related, $foreign_key = null, $owner_key = null) {
        global $wpdb;
        
        $related_model = new $related();
        $foreign_key = $foreign_key ?: $related_model->getForeignKey();
        $owner_key = $owner_key ?: $related_model->primary_key;
        
        return $related_model->where($owner_key, $this->$foreign_key)->first();
    }
    
    /**
     * HAS MANY Relationship
     * Example: User has many Posts
     */
    public function hasMany($related, $foreign_key = null, $local_key = null) {
        global $wpdb;
        
        $related_model = new $related();
        $foreign_key = $foreign_key ?: $this->getForeignKey();
        $local_key = $local_key ?: $this->primary_key;
        
        return $related_model->where($foreign_key, $this->$local_key);
    }
    
    /**
     * MANY TO MANY Relationship
     * Example: Post has many Tags through post_tag table
     */
    public function belongsToMany($related, $pivot_table = null, $foreign_pivot_key = null, $related_pivot_key = null) {
        global $wpdb;
        
        $related_model = new $related();
        
        // Generate pivot table name if not provided
        if (!$pivot_table) {
            $tables = [$this->getTableName(), $related_model->getTableName()];
            sort($tables);
            $pivot_table = implode('_', $tables);
        }
        
        $pivot_table = $wpdb->prefix . $pivot_table;
        $foreign_pivot_key = $foreign_pivot_key ?: $this->getForeignKey();
        $related_pivot_key = $related_pivot_key ?: $related_model->getForeignKey();
        
        return new BelongsToManyQuery(
            $this, 
            $related_model, 
            $pivot_table, 
            $foreign_pivot_key, 
            $related_pivot_key
        );
    }
    
    /**
     * Get foreign key name based on model name
     */
    protected function getForeignKey() {
        $class_name = get_class($this);
        $model_name = strtolower(substr($class_name, strrpos($class_name, '\\') + 1));
        return $model_name . '_id';
    }
    
    /**
     * Get table name without prefix
     */
    protected function getTableName() {
        global $wpdb;
        return str_replace($wpdb->prefix, '', $this->table);
    }
    
    // ... (Keep all previous methods: __get, __set, save, delete, find, all, etc.)
    
    /**
     * Eager load relationships when converting to array
     */
    public function toArray() {
        $data = $this->data;
        
        // Load eager loaded relationships
        foreach ($this->with as $relation) {
            if (method_exists($this, $relation)) {
                $data[$relation] = $this->$relation()->get()->toArray();
            }
        }
        
        return $data;
    }
}
```

### 2. BelongsToMany Query Builder for Many-to-Many Relationships

```php
<?php
/**
 * Many-to-Many Relationship Query Builder
 */
class BelongsToManyQuery {
    
    protected $parent;
    protected $related;
    protected $pivot_table;
    protected $foreign_pivot_key;
    protected $related_pivot_key;
    protected $wheres = [];
    
    public function __construct($parent, $related, $pivot_table, $foreign_pivot_key, $related_pivot_key) {
        $this->parent = $parent;
        $this->related = $related;
        $this->pivot_table = $pivot_table;
        $this->foreign_pivot_key = $foreign_pivot_key;
        $this->related_pivot_key = $related_pivot_key;
    }
    
    /**
     * Get related models
     */
    public function get() {
        global $wpdb;
        
        $sql = $wpdb->prepare("
            SELECT r.* 
            FROM {$this->related->table} AS r
            INNER JOIN {$this->pivot_table} AS p ON r.{$this->related->primary_key} = p.{$this->related_pivot_key}
            WHERE p.{$this->foreign_pivot_key} = %d
        ", $this->parent->{$this->parent->primary_key});
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        $collection = [];
        foreach ($results as $result) {
            $item = new $this->related($result);
            $item->exists = true;
            $collection[] = $item;
        }
        
        return $collection;
    }
    
    /**
     * Attach related model
     */
    public function attach($related_id, $pivot_data = []) {
        global $wpdb;
        
        $data = [
            $this->foreign_pivot_key => $this->parent->{$this->parent->primary_key},
            $this->related_pivot_key => $related_id
        ];
        
        // Merge additional pivot data
        $data = array_merge($data, $pivot_data);
        
        return $wpdb->insert($this->pivot_table, $data);
    }
    
    /**
     * Detach related model
     */
    public function detach($related_id = null) {
        global $wpdb;
        
        $where = [
            $this->foreign_pivot_key => $this->parent->{$this->parent->primary_key}
        ];
        
        if ($related_id) {
            $where[$this->related_pivot_key] = $related_id;
        }
        
        return $wpdb->delete($this->pivot_table, $where);
    }
    
    /**
     * Sync related models (replace all)
     */
    public function sync($related_ids) {
        global $wpdb;
        
        // First, detach all
        $this->detach();
        
        // Then attach new ones
        foreach ($related_ids as $related_id) {
            $this->attach($related_id);
        }
        
        return true;
    }
}
```

### 3. Enhanced QueryBuilder with Relationship Support

```php
<?php
/**
 * Enhanced Query Builder with Relationship Loading
 */
class QueryBuilder {
    
    protected $model;
    protected $wheres = [];
    protected $order_by = '';
    protected $limit = '';
    protected $offset = '';
    protected $with = [];
    
    public function __construct($model, $column = null, $operator = null, $value = null) {
        $this->model = $model;
        $this->with = $model->with;
        
        if ($column !== null) {
            $this->where($column, $operator, $value);
        }
    }
    
    // ... (Keep all previous query methods: where, orderBy, limit, offset, etc.)
    
    /**
     * Get results with relationships
     */
    public function get() {
        global $wpdb;
        
        $table = $this->model->table;
        $sql = "SELECT * FROM {$table}";
        
        // Add WHERE clauses
        if (!empty($this->wheres)) {
            $sql .= " WHERE ";
            $where_conditions = [];
            
            foreach ($this->wheres as $where) {
                $where_conditions[] = $wpdb->prepare(
                    "{$where['column']} {$where['operator']} %s", 
                    $where['value']
                );
            }
            
            $sql .= implode(' AND ', $where_conditions);
        }
        
        // Add ORDER BY, LIMIT, OFFSET
        if (!empty($this->order_by)) {
            $sql .= " {$this->order_by}";
        }
        
        if (!empty($this->limit)) {
            $sql .= " {$this->limit}";
        }
        
        if (!empty($this->offset)) {
            $sql .= " {$this->offset}";
        }
        
        $results = $wpdb->get_results($sql, ARRAY_A);
        
        $collection = [];
        foreach ($results as $result) {
            $item = new $this->model($result);
            $item->exists = true;
            
            // Load eager loaded relationships
            foreach ($this->with as $relation) {
                if (method_exists($item, $relation)) {
                    $item->data[$relation] = $item->$relation()->get();
                }
            }
            
            $collection[] = $item;
        }
        
        return $collection;
    }
}
```

## Complete Example: E-commerce Plugin Models

Let's create a complete example with multiple related models:

### 1. User Model

```php
<?php
class User extends BaseModel {
    
    protected $table = 'custom_users';
    protected $fillable = ['username', 'email', 'first_name', 'last_name', 'status'];
    
    /**
     * User has many orders
     */
    public function orders() {
        return $this->hasMany(Order::class);
    }
    
    /**
     * User has one profile
     */
    public function profile() {
        return $this->hasOne(UserProfile::class);
    }
    
    /**
     * User belongs to many roles (many-to-many)
     */
    public function roles() {
        return $this->belongsToMany(Role::class, 'user_roles');
    }
}
```

### 2. Order Model

```php
<?php
class Order extends BaseModel {
    
    protected $table = 'orders';
    protected $fillable = ['user_id', 'order_number', 'total_amount', 'status', 'created_at'];
    
    /**
     * Order belongs to a user
     */
    public function user() {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Order has many order items
     */
    public function items() {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Order belongs to many products through order items (many-to-many)
     */
    public function products() {
        return $this->belongsToMany(Product::class, 'order_items', 'order_id', 'product_id');
    }
}
```

### 3. Product Model

```php
<?php
class Product extends BaseModel {
    
    protected $table = 'products';
    protected $fillable = ['name', 'description', 'price', 'sku', 'stock_quantity', 'category_id'];
    
    /**
     * Product belongs to a category
     */
    public function category() {
        return $this->belongsTo(Category::class);
    }
    
    /**
     * Product has many order items
     */
    public function orderItems() {
        return $this->hasMany(OrderItem::class);
    }
    
    /**
     * Product belongs to many tags (many-to-many)
     */
    public function tags() {
        return $this->belongsToMany(Tag::class, 'product_tags');
    }
    
    /**
     * Product belongs to many orders through order items
     */
    public function orders() {
        return $this->belongsToMany(Order::class, 'order_items', 'product_id', 'order_id');
    }
}
```

### 4. Category Model

```php
<?php
class Category extends BaseModel {
    
    protected $table = 'categories';
    protected $fillable = ['name', 'slug', 'parent_id', 'description'];
    
    /**
     * Category has many products
     */
    public function products() {
        return $this->hasMany(Product::class, 'category_id');
    }
    
    /**
     * Category belongs to parent category (self-referencing)
     */
    public function parent() {
        return $this->belongsTo(Category::class, 'parent_id');
    }
    
    /**
     * Category has many child categories
     */
    public function children() {
        return $this->hasMany(Category::class, 'parent_id');
    }
}
```

### 5. OrderItem Model (Pivot model)

```php
<?php
class OrderItem extends BaseModel {
    
    protected $table = 'order_items';
    protected $fillable = ['order_id', 'product_id', 'quantity', 'price', 'subtotal'];
    
    /**
     * Order item belongs to an order
     */
    public function order() {
        return $this->belongsTo(Order::class);
    }
    
    /**
     * Order item belongs to a product
     */
    public function product() {
        return $this->belongsTo(Product::class);
    }
}
```

## Usage Examples with Relationships

### Basic Relationship Usage

```php
<?php
// Get user with their orders
$user = User::find(1);
$orders = $user->orders()->get();

// Get order with user and items
$order = Order::find(1);
$customer = $order->user(); // Single object
$items = $order->items()->get(); // Collection

// Get product with category and tags
$product = Product::find(1);
$category = $product->category();
$tags = $product->tags()->get();
```

### Eager Loading (N+1 Query Solution)

```php
<?php
// Eager load relationships to avoid N+1 queries
$orders = Order::with(['user', 'items', 'items.product'])->get();

foreach ($orders as $order) {
    echo "Order: " . $order->order_number . "<br>";
    echo "Customer: " . $order->user->first_name . "<br>";
    
    foreach ($order->items as $item) {
        echo "Product: " . $item->product->name . "<br>";
        echo "Quantity: " . $item->quantity . "<br>";
    }
}
```

### Many-to-Many Operations

```php
<?php
// Attach roles to user
$user = User::find(1);
$user->roles()->attach(2); // Attach role ID 2
$user->roles()->attach([3, 4, 5]); // Attach multiple roles

// Detach roles
$user->roles()->detach(3); // Detach specific role
$user->roles()->detach(); // Detach all roles

// Sync roles (replace all)
$user->roles()->sync([1, 2, 3]);

// Get user roles
$roles = $user->roles()->get();
```

### Complex Relationship Queries

```php
<?php
// Get all products in a specific category with their tags
$products = Product::with('tags')
                  ->where('category_id', 5)
                  ->where('stock_quantity', '>', 0)
                  ->orderBy('price', 'ASC')
                  ->get();

// Get users who have placed orders
$users = User::with(['orders' => function($query) {
    $query->where('status', 'completed')
          ->orderBy('created_at', 'DESC');
}])->get();

// Get orders with products and categories
$orders = Order::with(['items.product.category'])
              ->where('created_at', '>=', '2024-01-01')
              ->get();
```

### Database Tables Creation

```php
<?php
function create_relationship_tables() {
    global $wpdb;
    
    $charset_collate = $wpdb->get_charset_collate();
    
    // Users table
    $wpdb->query("CREATE TABLE {$wpdb->prefix}custom_users (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        email VARCHAR(255) NOT NULL,
        first_name VARCHAR(100),
        last_name VARCHAR(100),
        status VARCHAR(20) DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;");
    
    // Products table
    $wpdb->query("CREATE TABLE {$wpdb->prefix}products (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        sku VARCHAR(100) UNIQUE,
        stock_quantity INT DEFAULT 0,
        category_id BIGINT UNSIGNED,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;");
    
    // Orders table
    $wpdb->query("CREATE TABLE {$wpdb->prefix}orders (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT UNSIGNED NOT NULL,
        order_number VARCHAR(100) UNIQUE NOT NULL,
        total_amount DECIMAL(10,2) NOT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}custom_users(id)
    ) $charset_collate;");
    
    // Order items table (pivot table)
    $wpdb->query("CREATE TABLE {$wpdb->prefix}order_items (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        order_id BIGINT UNSIGNED NOT NULL,
        product_id BIGINT UNSIGNED NOT NULL,
        quantity INT NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        subtotal DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (order_id) REFERENCES {$wpdb->prefix}orders(id),
        FOREIGN KEY (product_id) REFERENCES {$wpdb->prefix}products(id)
    ) $charset_collate;");
    
    // User roles pivot table
    $wpdb->query("CREATE TABLE {$wpdb->prefix}user_roles (
        user_id BIGINT UNSIGNED NOT NULL,
        role_id BIGINT UNSIGNED NOT NULL,
        assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (user_id, role_id),
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}custom_users(id)
    ) $charset_collate;");
}
```

This enhanced ORM with relationship support provides:

- **All standard relationships**: hasOne, hasMany, belongsTo, belongsToMany
- **Eager loading** to solve N+1 query problems
- **Many-to-many operations** with attach/detach/sync
- **Fluent query interface** for complex relationship queries
- **Pivot table support** with additional data
- **Self-referencing relationships**

This architecture makes your WordPress plugin truly scalable and maintainable, allowing you to build complex data structures while keeping your code clean and object-oriented.




### 🔴