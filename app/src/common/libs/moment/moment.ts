// Moment overrides and additions

declare module moment {
    interface Duration {
        toJSON():string;
    }

    interface Moment {
        getFullYear():number;
        getMonth():number;
        getDate():number;
        getTime():string;
        setHours(...args):Moment;
    }
}


(<any>moment).duration.fn.toString = function() {
    return (<any>moment)(this.hours() + ':' + this.minutes() + ':' + this.seconds(), 'HH:mm:ss').format('HH:mm:ss');
};

(<any>moment).duration.fn.toJSON = (<any>moment).duration.fn.toString;

// Replace javascript Date object functions with moment ones.
// This allows moment object to be used with date pickers.
// See: DateLocaleProvider.prototype.$get:defaultFormatDate() in Angular Material source.
// Refer to app.ts for further datepicker configuration.

// These functions are required when the datepicker is opened
(<any>moment).fn.getFullYear = (<any>moment).fn.year;

(<any>moment).fn.getMonth = (<any>moment).fn.month;

(<any>moment).fn.getDate = (<any>moment).fn.date;

(<any>moment).fn.getTime = function() {
    return this.format('x'); // Returning time in milliseconds
};

(<any>moment).fn.setHours = (<any>moment).fn.hours;
