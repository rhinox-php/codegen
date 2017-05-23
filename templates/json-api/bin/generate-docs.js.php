const jsonRefs = require('json-refs');
const yaml = require('js-yaml');
const fs = require('fs');

const root = yaml.safeLoad(fs.readFileSync(__dirname + '/../docs/api.yml').toString());
jsonRefs.resolveRefs(root, {
    loaderOptions: {
        processContent: (resource, callback) => {
            callback(yaml.safeLoad(resource.text));
        },
    },
    includeInvalid: true,
    location: __dirname + '/../docs/api.yml',
}).then((results) => {
    fs.writeFileSync(__dirname + '/../docs/output/api.yml', yaml.dump(results.resolved));
}).catch((error) => {
    console.error(error);
});
