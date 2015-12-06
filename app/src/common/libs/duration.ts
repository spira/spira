namespace common.libs {

    (<any>moment).duration.fn.toString = function() {
        return moment(this.hours() + ':' + this.minutes() + ':' + this.seconds(), 'HH:mm:ss').format('HH:mm:ss');
    };

    (<any>moment).duration.fn.toJSON = (<any>moment).duration.fn.toString;

}