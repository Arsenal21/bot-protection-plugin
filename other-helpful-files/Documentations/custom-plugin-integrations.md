# Custom Plugin Integration
This document explains how you can integrate Cloudflare Turnstile to a form on your custom plugin.
___

## Initialization

First get the instance of the class that will handle rendering turnstile widget and verify the response token.

```php
$turnstile = BPCFT_Turnstile::get_instance();
```

Its better to check first if the Bot Protection Turnstile plugin is active:
```php
// Include the plugin.php file if it's not already included
if (!function_exists('is_plugin_active')) {
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if ( ! is_plugin_active('bot-protection-turnstile/bp-turnstile.php') ){
    return;
}
```


## Rendering Turnstile Widget
To render turnstile captcha widget implicitly in your form, use the following method:

```php
$turnstile->render_implicit( $callback, $action, $unique_id, $class_names );
```

Place this in the desired place inside your form on which you want to apply bot protections.

### Parameters Reference
| Parameter   | Description                                                                                                                                        |
|-------------|----------------------------------------------------------------------------------------------------------------------------------------------------|
| `callback`     | (Optional)<br/> A JavaScript callback invoked upon success of the challenge.                                                                       |
| `action`      | (Optional)<br/> A custom value that can be used to differentiate widgets. Can contain upto 32 alphanumeric characters including _ and -            |
| `unique_id`  | (Optional)<br/> Generates the id attribute for the widget container like `cf-turnstile-<unique_id>`. Useful if you want to target specific widget. |
| `class_names`  | (Optional)<br/> CSS classes to add to the widget container. Useful for styling purpose.                                                            |

<b>Note:</b> We currently don't have the support for explicit rendering of widget.

## Validate Captcha Response

When the form is submitted, validate captcha token response. For this, just run the following:

```php

$result = $turnstile->check_cft_token_response();
```

Running this method returns an array containing validation result:

For successful validation:
```php
Array
(
    [success] => true
)
```

On validation error:
```php
Array
(
    [success] => false
    [error_code] => <error-code>
    [error_message] => <error-message>
)
```

You can then use this `$result` array to validate you form. 

## Example Integration

Here is an demo how you can use it in your custom plugin form:

```php
$turnstile = BPCFT_Turnstile::get_instance();

if ( isset['submit'] ) {
    // Verify captcha response.
    $cft_response = $turnstile->check_cft_token_response();
    $success = isset( $cft_response['success'] ) ? boolval( $cft_response['success'] ) : false;
    if ( ! $success ) {
        // Show error message.
        wp_die($cft_response['error_message']);
    }
    
    // else, continue form submission handling...
}
?>

<form action="" method="post">
    <input type="email" name="email" value="">
    <input type="password" name="password" value="">
    <?php $turnstile->render_implicit('my_callback', 'my_form', wp_rand(), 'my_css_class'); ?>
    <button type="submit">Submit</button>
</form>
```