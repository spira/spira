declare module moment {
    interface MomentDate extends Moment {
    }
}

function momentDate(...args) {
    var self = (<any>moment)(...args);

    (<any>self).__proto__ = momentDate.prototype;

    return self;
}

momentDate.prototype.__proto__ = (<any>moment).prototype;

momentDate.prototype.toString = function () {
    return this.format('ddd MMM DD YYYY');
};

momentDate.prototype.toISOString = function () {
    return this.format('YYYY-MM-DD');
};

momentDate.prototype.toJSON = function () {
    return this.format('YYYY-MM-DD');
};