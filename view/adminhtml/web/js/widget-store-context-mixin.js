define(['uiRegistry'], function (registry) {
    'use strict';

    return function (WidgetBuilder) {
        return WidgetBuilder.extend({
            serializeElements: function () {
                var params = this._super.apply(this, arguments);
                var page = registry.get('cms_page_form.page_form_data_source');
                var block = registry.get('cms_block_form.block_form_data_source');
                var provider = page || block;
                if (provider) {
                    var stores = provider.get('data.store_id');
                    if (stores !== undefined && stores !== null) {
                        stores = Array.isArray(stores) ? stores : [stores];
                        params.widgetkit_store_id = stores.map(Number).find(function (id) { return id > 0; }) || 0;
                    }
                    params[page ? 'page_id' : 'block_id'] = provider.get(page ? 'data.page_id' : 'data.block_id') || 0;
                }
                // Context is a top-level request field, never a persisted widget parameter.
                return params;
            }
        });
    };
});
