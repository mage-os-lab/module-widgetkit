const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
let mixin;
let data = {'data.store_id': ['2'], 'data.page_id': 42};
const registry = {get: name => name === 'cms_page_form.page_form_data_source' ? {get: key => data[key]} : null};
vm.runInNewContext(fs.readFileSync(require('node:path').join(__dirname, '../../view/adminhtml/web/js/widget-store-context-mixin.js'), 'utf8'), {
    define: (_, factory) => { mixin = factory(registry); }
});
const extended = mixin({extend: value => value});
const params = {'parameters[title]': 'Title'};
const context = {_super: () => ({...params})};
let result = extended.serializeElements.call(context, []);
assert.equal(result.widgetkit_store_id, 2);
assert.equal(result.page_id, 42);
assert.equal(result['parameters[title]'], 'Title');
data['data.store_id'] = ['0'];
result = extended.serializeElements.call(context, []);
assert.equal(result.widgetkit_store_id, 0);
assert.equal(Object.keys(result).filter(key => key.startsWith('parameters')).length, 1);
console.log('PageBuilder store context: 5 assertions passed');
