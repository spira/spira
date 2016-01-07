describe('Moment', () => {

    describe('Moment overrides and additions', () => {

        it('should be able to get the full year', () => {

            expect(moment('1988-05-14').getFullYear()).to.equal(1988);

        });

        it('should be able to get the month', () => {

            expect(moment('1988-05-14').getMonth()).to.equal(4);

        });

        it('should be able to get the date', () => {

            expect(moment('1988-05-14').getDate()).to.equal(14);

        });

        it('should be able to get the time', () => {

            expect(moment(111222333444555).getTime()).to.equal('111222333444555');

        });

        it('should be able to set hours', () => {

            expect(moment('1988-05-14 13:00:05').setHours(14).hours()).to.equal(14);

        });

    });

    describe('Moment duration overrides and additions', () => {

        it('should be able to format into a string', () => {

            expect(moment.duration(11111111).toString()).to.equal('03:05:11');

        });

        it('should be able to format to JSON', () => {

            expect(moment.duration(11111111).toJSON()).to.equal('03:05:11');

        });

    });

    describe('MomentDate function', () => {

        it('should be able to create a new moment date object', () => {

            let mDate = momentDate('1988-05-14');

            expect(mDate).to.be.instanceOf(moment);

        });

        it('should have standard moment functions', () => {

            let mDate = momentDate('1988-05-14');
            let m = moment('1988-05-14');

            expect(mDate.toISOString()).to.equal(m.toISOString());
            expect(mDate.hours()).to.equal(m.hours());

        });

        it('should have a different toString function', () => {

            let mDate = momentDate('1988-05-14');
            let m = moment('1988-05-14');

            expect(mDate.toString()).to.not.equal(m.toString());
            expect(mDate.toString()).to.equal('1988-05-14');

            // Timezone is included in this output
            expect(m.toString()).to.have.string('Sat May 14 1988 00:00:00');

        });


    });


});