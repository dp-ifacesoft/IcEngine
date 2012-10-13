function Class () { };
Class.prototype.initialize = function () { };
Class.extend = function (def)
{
    var classDef = function ()
    {
        if (arguments [0] !== Class)
        {
            this.initialize.apply (this, arguments);
        }
    };
    var proto = new this (Class);
    var superClass = this.prototype;
	
    for (var n in def)
    {
        var item = def [n];
        if (item instanceof Function)
        {
            item._name = n;
        //item.$ = superClass;
        }
        else
        {
            classDef [n] = item;
        }
        proto [n] = item;
    }
    classDef.prototype = proto;
    classDef.prototype.parent = function () {
        var name = arguments.callee.caller._name;
        superClass [name].apply (this, arguments);
	};
    classDef.extend = this.extend;
	
	return classDef;
};