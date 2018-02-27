module.exports = {
    "env": {
        "node": true,
        "es6": true
    },
    "extends": "eslint:recommended",
    "parserOptions": {
        "sourceType": "module"
    },
    "rules": {
        "arrow-parens": [
          "error",
          "always"
        ],
        "arrow-body-style": [
            2,
            "as-needed"
        ],
        "comma-dangle": [
            2,
            "always-multiline"
        ],
        "import/imports-first": 0,
        "import/newline-after-import": 0,
        "import/no-dynamic-require": 0,
        "import/no-extraneous-dependencies": 0,
        "import/no-named-as-default": 0,
        "import/prefer-default-export": 0,
        "indent": [
            2,
            2,
            {
                "SwitchCase": 1
            }
        ],
        "max-len": 0,
        "newline-per-chained-call": 0,
        "no-confusing-arrow": 0,
        "no-console": 1,
        "no-use-before-define": 0,
        "prefer-template": 2,
        "class-methods-use-this": 0,
        "require-yield": 0,
        "eol-last": 1,
        "linebreak-style": [
            "error",
            "unix"
        ],
        "quotes": [
            "error",
            "single"
        ],
        "semi": [
            "error",
            "always"
        ]
    }
};
