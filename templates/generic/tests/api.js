const assert = require('assert');
const request = require('request');
const fs = require('fs');
const env = require('dotenv').config({
    path: __dirname + '/../.env',
});

module.exports = new class {
    constructor() {
        this.baseUrl = env.parsed.TEST_BASE_URL;
        this.idNotFound = 1000000;
    }

    auth() {
        return Promise.resolve();
    }

    validateJsonApi(response) {
        assert(response, 'Response was empty.');
        assert(response.data, 'Response data was empty.');
        if (Array.isArray(response.data)) {
            response.data.forEach(entity => this.validateJsonApiEntity(entity));
        } else {
            this.validateJsonApiEntity(response.data);
        }
    }

    validateJsonApiEntity(entity) {
        assert(entity.id, 'Response data.id was empty.');
        assert(entity.type, 'Response data.type was empty.');
    }

    dump() {
        for (let i = 0; i < arguments.length; i++) {
            if (typeof arguments[i] == 'object') {
                console.log(JSON.stringify(arguments[i], null, 4));
            } else {
                console.log(arguments[i]);
            }
        }
        process.exit(1);
    }

    log() {
        for (let i = 0; i < arguments.length; i++) {
            if (typeof arguments[i] == 'object') {
                console.log(JSON.stringify(arguments[i], null, 4));
            } else {
                console.log(arguments[i]);
            }
        }
    }

    handleError(done) {
        return (error) => {
            this.log('ERROR', error);
            done(error);
        };
    }

    request(method, url, params, expectedStatus, file) {
        params = params || {};
        return new Promise((resolve, reject) => {
            this.log(method.toUpperCase() + ' ' + this.baseUrl + url, params || '(no request data)');
            const requestOptions = {
                json: true,
                timeout: 30000,
                headers: {
                    'User-Agent': 'API Test',
                },
            };
            if (file) {
                params = params || {};
                for (let key in params) {
                    if (params[key] === null) {
                        delete params[key];
                    }
                }
                params.file = fs.createReadStream(file);
                requestOptions.formData = params;
            } else {
                requestOptions.body = params;
            }

            const r = request[method](this.baseUrl + url, requestOptions, (error, response, body) => {
                assert.equal(error, null, 'Error returned from request: ' + error);
                this.log('RESPONSE ' + response.statusCode, body || '(empty response body)');

                // this.log(response.headers);
                if (expectedStatus !== false) {
                    assert.equal(response.statusCode, expectedStatus || 200, 'Status code should be ' + (expectedStatus || 200));
                    if (!expectedStatus) {
                        assert.equal(typeof body, 'object', 'Expected result to be an object.');
                    }
                }

                resolve(body);
            });
        });
    }

    get(url, params, expectedStatus) {
        return this.request('get', url, params, expectedStatus);
    }

    post(url, params, expectedStatus) {
        return this.request('post', url, params, expectedStatus);
    }

    patch(url, params, expectedStatus) {
        return this.request('patch', url, params, expectedStatus);
    }

    delete(url, params, expectedStatus) {
        return this.request('delete', url, params, expectedStatus);
    }

    put(url, params, expectedStatus) {
        return this.request('put', url, params, expectedStatus);
    }

    upload(url, file, params, expectedStatus) {
        return this.request('post', url, params, expectedStatus, file);
    }
};
