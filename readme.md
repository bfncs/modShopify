# modX Extra: modShopify

*******************************************

* Extra: modShopify
* Developer: Marc Loehe (@boundaryfunc)
* Version: 1.0-alpha

*******************************************

A modX extra to show products via Shopify Products.

# Installation

1. Install phpThumbOf-Extra.
2. In the backend of your shop, create a new private app.
3. Install modShopify-Extra.
4. Enter credentials (Api Key, Password, Shared Token) for your private Shopify App while installing or later in modX' system settings.
5. You can now use the snippet `[[showProducts]]`.

# Usage

    [[showProducts]]
    
## Options:

 * `&limit`: Amount of results (default: 50, max. 250)
 * `&page`: Page of results to show (default: 1)
 * `&published_status`: Show only published products (default: published, possible values: published|unpublished|any)
 * `&vendor`: Filter products by vendor
 * `&handle`: Filter products by handle
   
 * `&containerTpl`: Tpl Chunk for the outer container
 * `&productTpl`: Tpl Chunk for a single product
 * `&productImgTpl`: Tpl Chunk for a single product image
 * `&productVariantTpl`: Tpl Chunk for a single product variant
     
 * `&thumbsWidth`: Width of thumbs
 * `&thumbsHeight`: Height of thumbs
 * `&thumbsArgs`: Additional Arguments for phpThumbOf
    
