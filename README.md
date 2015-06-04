# Mediaflow plugin for Craft
Craft CMS Keyteq Mediaflow plugin. Read more [Visit Mediaflow!](http://getmediaflow.com)

## Install

### The easy and clean way

1. `composer require mediaflow/mediaflow-craft:~1.0`
2. Modify your `public/index.php` to autoload depdendencies by adding this as the second to last line:

    ```php
    require __DIR__ . '/../vendor/autoload.php';
    ```

    This is only required once for all plugins using composer

### The hard Crafty way

1. Open [the list of releases](https://github.com/KeyteqLabs/mediaflow-craft/releases/)
2. Download the zip file for the desired release
3. Unpack the zip file to `plugins/mediaflow/`

## Example of usage

## Definition of crop sizes
You can define crop sizes in the Mediaflow custom fields
```json
{"main":[400,500],"list-view":[500,300],"thumb":[100,100],"content-head":[800,316]}
```
In your template you can do the following:
```twig
<img src="{{ entry.yourImage.url('list-view') }}" />
```
### Generating a media preview URL

```smarty
<img src="{{entry.mediaflowField.url({width: 100,height: 100}) }}" />
```

### Example with Foundation Interchange
```smarty
{% set media = entry.mediaflowField %}
<img data-interchange="
    [{{ media.url({width:width,height:height,quality:90}) }}, (default)],
    [{{ media.url({width:width,height:height,quality:90}) }}, (large)]
">
<noscript>
    <img src="{{media.url({width:width,height:height,quality:90}) }}">
</noscript>
```

If you use Interchange and have set crops for your image you can
have Mediaflow build the interchange string straightup for you by specifying the crop aliases:

```smarty
<img data-interchange="{{media.interchange(['default','large'])}}">
```
