const path = require('path');
const Twig = require('..').factory();

const {twig} = Twig;

describe('Twig.js Functions ->', function () {
    // Add some test functions to work with
    Twig.extendFunction('echo', a => {
        return a;
    });
    Twig.extendFunction('square', a => {
        return a * a;
    });
    Twig.extendFunction('list', (...args) => {
        return Array.prototype.slice.call(args);
    });
    Twig.extendFunction('include', function (_) {
        (typeof this).should.equal('object');
        this.should.not.equal(global, 'function should not be called on global');
        (typeof this.context).should.equal('object');

        return 'success';
    });

    it('should allow you to define a function', function () {
        twig({data: '{{ square(a) }}'}).render({a: 4}).should.equal('16');
    });
    it('should chain with other expressions', function () {
        twig({data: '{{ square(a) + 4 }}'}).render({a: 4}).should.equal('20');
    });
    it('should chain with filters', function () {
        twig({data: '{{ echo(a)|default("foo") }}'}).render().should.equal('foo');
    });
    it('should work in for loop expressions', function () {
        twig({data: '{% for i in list(1, 2, 3) %}{{ i }},{% endfor %}'}).render().should.equal('1,2,3,');
    });
    it('should be able to differentiate between a function and a variable', function () {
        twig({data: '{{ square ( square ) + square }}'}).render({square: 2}).should.equal('6');
    });
    it('should work with boolean operations', function () {
        twig({data: '{% if echo(true) or echo(false) %}yes{% endif %}'}).render().should.equal('yes');
    });

    it('should call function on template instance', function () {
        const macro = '{% macro testMacro(data) %}success{% endmacro %}';
        const tpl = '{% import "testMacro" as m %}{{ m.testMacro({ key: include() }) }}';

        twig({data: macro, id: 'testMacro'});
        twig({data: tpl, allowInlineIncludes: true}).render().should.equal('success');
    });

    it('should execute functions passed as context values', function () {
        twig({
            data: '{{ value }}'
        }).render({
            value() {
                return 'test';
            }
        }).should.equal('test');
    });
    it('should execute functions passed as context values with this mapped to the context', function () {
        twig({
            data: '{{ value }}'
        }).render({
            test: 'value',
            value() {
                return this.test;
            }
        }).should.equal('value');
    });
    it('should execute functions passed as context values with arguments', function () {
        twig({
            data: '{{ value(1, "test") }}'
        }).render({
            value(a, b, c) {
                return a + '-' + b + '-' + (c === undefined ? 'true' : 'false');
            }
        }).should.equal('1-test-true');
    });
    it('should execute functions passed as context value parameters with this mapped to the context', function () {
        twig({
            data: '{{ value }}'
        }).render({
            test: 'value',
            value() {
                return this.test;
            }
        }).should.equal('value');
    });

    it('should execute functions passed as context object parameters', function () {
        twig({
            data: '{{ obj.value }}'
        }).render({
            obj: {
                test: 'value',
                value() {
                    return this.test;
                }
            }
        }).should.equal('value');
    });
    it('should execute functions passed as context object parameters with arguments', function () {
        twig({
            data: '{{ obj.value(1, "test") }}'
        }).render({
            obj: {
                test: 'value',
                value(a, b, c) {
                    return a + '-' + b + '-' + this.test + '-' + (c === undefined ? 'true' : 'false');
                }
            }
        }).should.equal('1-test-value-true');
    });

    it('should execute functions passed as context object parameters', function () {
        twig({
            data: '{{ obj["value"] }}'
        }).render({
            obj: {
                value() {
                    return 'test';
                }
            }
        }).should.equal('test');
    });
    it('should execute functions passed as context object parameters with arguments', function () {
        twig({
            data: '{{ obj["value"](1, "test") }}'
        }).render({
            obj: {
                value(a, b, c) {
                    return a + '-' + b + '-' + (c === undefined ? 'true' : 'false');
                }
            }
        }).should.equal('1-test-true');
    });

    describe('Built-in Functions ->', function () {
        describe('range ->', function () {
            it('should work over a range of numbers', function () {
                twig({data: '{% for i in range(0, 3) %}{{ i }},{% endfor %}'}).render().should.equal('0,1,2,3,');
            });
            it('should work over a range of letters', function () {
                twig({data: '{% for i in range("a", "c") %}{{ i }},{% endfor %}'}).render().should.equal('a,b,c,');
            });
            it('should work with an interval', function () {
                twig({data: '{% for i in range(1, 15, 3) %}{{ i }},{% endfor %}'}).render().should.equal('1,4,7,10,13,');
            });

            it('should work with .. invocation', function () {
                twig({data: '{% for i in 0..3 %}{{ i }},{% endfor %}'}).render().should.equal('0,1,2,3,');
                twig({data: '{% for i in "a" .. "c" %}{{ i }},{% endfor %}'}).render().should.equal('a,b,c,');
            });
        });
        describe('cycle ->', function () {
            it('should cycle through an array of values', function () {
                twig({data: '{% for i in range(0, 3) %}{{ cycle(["odd", "even"], i) }};{% endfor %}'}).render().should.equal('odd;even;odd;even;');
            });
        });
        describe('date ->', function () {
            function pad(num) {
                return num < 10 ? '0' + num : num;
            }

            function stringDate(date) {
                return pad(date.getDate()) + '/' + pad(date.getMonth() + 1) + '/' + date.getFullYear() +
                                         ' @ ' + pad(date.getHours()) + ':' + pad(date.getMinutes()) + ':' + pad(date.getSeconds());
            }

            it('should understand timestamps', function () {
                const date = new Date(946706400 * 1000);
                twig({data: '{{ date(946706400)|date("d/m/Y @ H:i:s") }}'}).render().should.equal(stringDate(date));
            });
            it('should understand relative dates', function () {
                twig({data: '{{ date("+1 day") > date() }}'}).render().should.equal('true');
                twig({data: '{{ date("-1 day") > date() }}'}).render().should.equal('false');
            });
            it('should support \'now\' as a date parameter', function () {
                twig({data: '{{ date("now") }}'}).render().should.equal(new Date().toString());
            });
            it('should understand exact dates', function () {
                const date = new Date('December 17, 1995 08:24:00');
                twig({data: '{{ date("December 17, 1995 08:24:00")|date("d/m/Y @ H:i:s") }}'}).render().should.equal(stringDate(date));
            });
        });
        describe('dump ->', function () {
            const EOL = '\n';
            it('should output formatted number', function () {
                twig({data: '{{ dump(test) }}'}).render({test: 5}).should.equal('number(5)' + EOL);
            });
            it('should output formatted string', function () {
                twig({data: '{{ dump(test) }}'}).render({test: 'String'}).should.equal('string(6) "String"' + EOL);
            });
            it('should output formatted boolean', function () {
                twig({data: '{{ dump(test) }}'}).render({test: true}).should.equal('bool(true)' + EOL);
            });
            it('should output formatted null', function () {
                twig({data: '{{ dump(test) }}'}).render({test: null}).should.equal('NULL' + EOL);
            });
            it('should output formatted object', function () {
                twig({data: '{{ dump(test) }}'}).render({test: {}}).should.equal('object(0) {' + EOL + '}' + EOL);
            });
            it('should output formatted array', function () {
                twig({data: '{{ dump(test) }}'}).render({test: []}).should.equal('object(0) {' + EOL + '}' + EOL);
            });
            it('should output formatted undefined', function () {
                twig({data: '{{ dump(test) }}'}).render({test: undefined}).should.equal('undefined' + EOL);
            });
        });

        describe('block ->', function () {
            it('should render the content of blocks', function () {
                twig({data: '{% block title %}Content - {{ val }}{% endblock %} Title: {{ block("title") }}'}).render({val: 'test'})
                    .should.equal('Content - test Title: Content - test');
            });

            it('shouldn\'t escape the content of blocks twice', function () {
                twig({
                    autoescape: true,
                    data: '{% block test %}{{ val }}{% endblock %} {{ block("test") }}'
                }).render({
                    val: 'te&st'
                }).should.equal('te&amp;st te&amp;st');
            });
        });

        describe('attribute ->', function () {
            it('should access attribute of an object', function () {
                twig({data: '{{ attribute(obj, key) }}'}).render({
                    obj: {name: 'Twig.js'},
                    key: 'name'
                })
                    .should.equal('Twig.js');
            });

            it('should call function of attribute of an object', function () {
                twig({data: '{{ attribute(obj, key, params) }}'}).render({
                    obj: {
                        name(first, last) {
                            return first + '.' + last;
                        }
                    },
                    key: 'name',
                    params: ['Twig', 'js']
                })
                    .should.equal('Twig.js');
            });

            it('should return undefined for missing attribute of an object', function () {
                twig({data: '{{ attribute(obj, key, params) }}'}).render({
                    obj: {
                        name(first, last) {
                            return first + '.' + last;
                        }
                    },
                    key: 'missing',
                    params: ['Twig', 'js']
                })
                    .should.equal('');
            });

            it('should return element of an array', function () {
                twig({data: '{{ attribute(arr, 0) }}'}).render({
                    arr: ['Twig', 'js']
                })
                    .should.equal('Twig');
            });

            it('should return undef for array beyond index size', function () {
                twig({data: '{{ attribute(arr, 100) }}'}).render({
                    arr: ['Twig', 'js']
                })
                    .should.equal('');
            });

            it('should return undef for undefined object', function () {
                twig({data: '{{ attribute(arr, "bar") }}'}).render({})
                    .should.equal('');
            });
        });
        describe('template_from_string ->', function () {
            it('should load a template from a string', function () {
                twig({data: '{% include template_from_string("{{ value }}") %}'}).render({
                    value: 'test'
                })
                    .should.equal('test');
            });
            it('should load a template from a variable', function () {
                twig({data: '{% include template_from_string(template) %}'}).render({
                    template: '{{ value }}',
                    value: 'test'
                })
                    .should.equal('test');
            });
        });

        describe('random ->', function () {
            this.timeout(500);

            it('should return a random item from a traversable or array', function () {
                const arr = 'bcdefghij'.split('');

                for (let i = 1; i <= 1000; i++) {
                    arr.should.containEql(twig({data: '{{ random(arr) }}'}).render({arr}));
                }
            });

            it('should return a random character from a string', function () {
                const str = 'abcdefghij';

                for (let i = 1; i <= 1000; i++) {
                    str.should.containEql(twig({data: '{{ random(str) }}'}).render({str}));
                }
            });

            it('should return a random integer between 0 and the integer parameter', function () {
                for (let i = 1; i <= 1000; i++) {
                    twig({data: '{{ random(10) }}'}).render().should.be.within(0, 10);
                }
            });

            it('should return a random integer between 0 and 2147483647 when no parameters are passed', function () {
                for (let i = 1; i <= 1000; i++) {
                    twig({data: '{{ random() }}'}).render().should.be.within(0, 2147483647);
                }
            });
        });

        describe('min, max ->', function () {
            it('should support the \'min\' function', function () {
                twig({data: '{{ min(2, 1, 3, 5, 4) }}'}).render().should.equal('1');
                twig({data: '{{ min([2, 1, 3, 5, 4]) }}'}).render().should.equal('1');
                twig({data: '{{ min({2:"two", 1:"one", 3:"three", 5:"five", 4:"four"}) }}'}).render().should.equal('five');
            });

            it('should support the \'max\' function', function () {
                twig({data: '{{ max([2, 1, 3, 5, 4]) }}'}).render().should.equal('5');
                twig({data: '{{ max(2, 1, 3, 5, 4) }}'}).render().should.equal('5');
                twig({data: '{{ max({2:"two", 1:"one", 3:"three", 5:"five", 4:"four"}) }}'}).render().should.equal('two');
            });
        });

        describe('source ->', function () {
            it('should allow loading an absolute path', function () {
                twig({data: '{{ source("' + path.join(__dirname, '/templates/simple.twig') + '") }}'}).render().should.equal('Twig.js!');
            });

            it('should allow loading relative paths', function () {
                twig({data: '{{ source("test/templates/simple.twig") }}'}).render().should.equal('Twig.js!');
            });

            it('should allow loading paths with namespaces', function () {
                twig({
                    'data': "{{ source('test::namespaces.twig') }}",
                    'namespaces': {
                        'test': 'test/templates/functions/source',
                    },
                }).render().trim().should.equal('{{ test }}');
            });
        });
    });
});
