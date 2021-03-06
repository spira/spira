<?php

/*
 * This file is part of the Spira framework.
 *
 * @link https://github.com/spira/spira
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

class ApiUserController extends UserController
{
    /**
     * Connect a user from a foreign system.
     *
     * @throws Gdn_UserException
     *
     * @return void
     */
    public function sso()
    {
        // Prepare Data
        $data = $this->Form->formDataSet()[0];
        $provider = Gdn_AuthenticationProviderModel::getProviderByScheme('jsconnect');

        $default = [
            'Name' => '',
            'Email' => '',
            'Photo' => '',
            'Roles' => [8],
        ];

        $user = array_intersect_key($data, $default) + $default;
        $uniqueId = val('UniqueID', $data);
        $clientId = $provider['AuthenticationKey'];

        // Create or get existing user
        $id = Gdn::userModel()->connect($uniqueId, $clientId, $user);
        if (! $id) {
            throw new Gdn_UserException("Invalid user id (`$id` given)", 400);
        }

        // Get relevant user information
        $user = Gdn::userModel()->getID($id, DATASET_TYPE_ARRAY);
        $user = array_intersect_key($user, array_flip(['UserID', 'Name']));

        $this->setData('User', $user, false);
        $this->render();
    }

    /**
     * Delete hotfix.
     *
     * The API module transforms the method to uppercase, which makes Vanilla
     * not recognize it.
     *
     * @param  int    $UserID
     * @param  string $Method
     *
     * @return void
     */
    public function delete($UserID = '', $Method = '')
    {
        parent::delete($UserID, strtolower($Method));
    }
}
