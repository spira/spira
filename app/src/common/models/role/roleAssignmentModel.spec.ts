namespace common.models {

    describe('Role Assignment Model', () => {

        let assignmentData = _.clone(RoleAssignmentMock.entity());

        it('should instantiate a new role', () => {

            let role = new common.models.RoleAssignment(assignmentData);

            expect(role).to.be.instanceOf(common.models.RoleAssignment);
        });

    });

}