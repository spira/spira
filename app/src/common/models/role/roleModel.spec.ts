namespace common.models {

    describe('Role Model', () => {

        let data = _.clone(RoleMock.entity());

        it('should instantiate a new role', () => {

            let role = new common.models.Role(data);

            expect(role).to.be.instanceOf(common.models.Role);
        });

    });

}