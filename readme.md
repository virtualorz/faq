# Installation #

### install by composer ###
<pre><code>
composer require virtualorz/faq
</code></pre>

### edit config/app.php ###
<pre><code>
'providers' => [
    ...
    Virtualorz\Fileupload\FileuploadServiceProvider::class,
    Virtualorz\Cate\CateServiceProvider::class,
    Virtualorz\Faq\FaqServiceProvider::class,
    ...
]

'aliases' => [
    ...
    'Fileupload' => Virtualorz\Fileupload\FileuploadFacade::class,
    'Cate' => Virtualorz\Cate\CateFacade::class,
    'Faq' => Virtualorz\Faq\FaqFacade::class,
    ...
]
</code></pre>

### migration db table ###
<pre><code>
php artisan migrate
</code></pre>

# usage #
#### 1. get cate list data ####
<pre><code>
$dataArray = Faq::list('use type');
</code></pre>
use type : eg. news, member , product ...etc, different type in your application
$dataArray : return array in two elements : [$dataArry,pagination elements,page item]

#### 2. add data to cate ####
<pre><code>
Faq::add('use type');
</code></pre>
with request variable name required : faq-cate_id,faq-title,faq-answer,faq-order,faq-enable

#### 3. get cate detail ####
<pre><code>
$dataRow = Faq::detail($faq_id);
</code></pre>

#### 4. edit data to cate ####
<pre><code>
Faq::edit();
</code></pre>
with request variable name required : faq-cate_id,faq-title,faq-answer,faq-order,faq-enable

#### 5. delete cate data ####
<pre><code>
Faq::delete();
</code></pre>
with request variable name required : id as integer or id as array

#### 6. enable cate data ####
<pre><code>
Faq::enable($type);
</code></pre>
with request variable name required : id as integer or id as array
$type is 0 or1 , 0 to disable i to enable




