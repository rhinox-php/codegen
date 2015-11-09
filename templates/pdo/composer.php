{
    "minimum-stability": "dev",
    "prefer-stable": true,
    "require": {
        "nikic/fast-route": "^0.6.0",
        "symfony/http-foundation": "^2.7",
        "symfony/validator": "^2.7",
        "rhinox/core": "dev-master",
        "rhinox/data-table": "dev-master",
        "filp/whoops": "dev-php7"
    },
    "repositories": [
        {
            "type": "vcs",
            "url": "git@bitbucket.org:PetahNZ/rhino-core"
        },
        {
            "type": "vcs",
            "url": "git@bitbucket.org:PetahNZ/rhino-core"
        },
        {
            "type": "vcs",
            "url": "git@bitbucket.org:PetahNZ/rhino-debug"
        },
        {
            "type": "vcs",
            "url": "git@bitbucket.org:PetahNZ/rhino-data-table"
        }
    ],
    "autoload": {
        "psr-4": {
            <?= json_encode($codegen->getNamespace() . '\\'); ?>: "classes"
        }
    }
}
