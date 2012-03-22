<?php

/**
 * Zimbra SOAP API calls.
 *
 * @author LiberSoft <info@libersoft.it>
 * @license http://www.gnu.org/licenses/gpl.txt
 */

require_once(dirname(__FILE__).'/model/lsZimbraObject.class.php');
require_once(dirname(__FILE__).'/model/ZimbraAccount.class.php');
require_once(dirname(__FILE__).'/model/ZimbraDistributionList.class.php');
require_once(dirname(__FILE__).'/model/ZimbraServer.class.php');
require_once(dirname(__FILE__).'/model/ZimbraDomain.class.php');
require_once(dirname(__FILE__).'/ZimbraSOAP.php');
require_once(dirname(__FILE__).'/ZimbraException.php');

class ZimbraAdmin
{

    private $authToken;
    private $zimbraConnect;
    private $hideSystemUsers = true;
    private $systemUsersRegexp = array(
        "admin",
        "postmaster",
        "ham.*",
        "spam.*",
        "virus-quarantine.*",
        "galsync"
    );

    public function __construct($server, $port = 7071, $authToken = null)
    {
        $this->zimbraConnect = new ZimbraSOAP($server, $port);

        if ($authToken) {
            $this->authToken = $authToken;
            $this->zimbraConnect->addContextChild('authToken', $this->authToken);
        }
    }

    public function auth($email, $password)
    {
        $SOAPMessage = $this->zimbraConnect;
        $xml = $SOAPMessage->request('AuthRequest', array('name' => $email, 'password' => $password));

        $this->authToken = $xml->children()->AuthResponse->authToken;
        $this->zimbraConnect->addContextChild('authToken', $this->authToken);

        return (string) $this->authToken;
    }

    public function searchDirectory($domain, $limit = 10, $offset = 0, $type = 'accounts', $sort = null, $query = null)
    {
        if ($this->hideSystemUsers && $type == 'accounts') {
            $ldapQuery = "&amp;(";
            if ($query !== null) {
                $ldapQuery .= "(name=$query*)";
            }
            foreach ($this->systemUsersRegexp as $account) {
                $ldapQuery .= "(!(name=$account))";
            }
            $ldapQuery .= ')';
        } else {
            $ldapQuery = '';
        }

        $attributes = array(
            'limit'  => $limit,
            'offset' => $offset,
            'domain' => $domain,
            'types'  => $type,
        );

        if (!is_null($sort)) {
            $attributes['sortBy'] = $sort[0];
            $attributes['sortAscending'] = $sort[1];
        }

        return $this->zimbraConnect->request('SearchDirectoryRequest', $attributes, array('query' => $ldapQuery));
    }

    public function getAccounts(array $attributes, $sort = null, $query = null)
    {
        $accounts = $this->searchDirectory($attributes['domain'], $attributes['limit'], $attributes['offset'], 'accounts', $sort, $query);

        $results = array();

        foreach ($accounts->children()->SearchDirectoryResponse->children() as $account) {
            $results[] = new ZimbraAccount($account);
        }

        return $results;
    }

    public function count($domain, $query = null)
    {
        $response = $this->searchDirectory($domain, 1, 0, 'accounts', null, $query);

        return $response->children()->SearchDirectoryResponse['searchTotal'];
    }

    public function deleteAccount($id)
    {
        $this->zimbraConnect->request('DeleteAccountRequest', array(), array('id' => $id));

        return true;
    }

    public function setPassword($id, $password)
    {
        $params = array(
            'id' => $id,
            'newPassword' => $password,
        );
        $this->zimbraConnect->request('SetPasswordRequest', array(), $params);
    }

    private function getQuotaUsage(array $attributes, $targetServer = null)
    {
        if (isset($targetServer)) {
            $this->zimbraConnect->addContextChild('targetServer', $targetServer);
        }

        return $this->zimbraConnect->request('GetQuotaUsageRequest', $attributes);
    }

    private function getAllDomains()
    {
        return $this->zimbraConnect->request('GetAllDomainsRequest');
    }

    public function getDomains()
    {
        foreach ($this->getAllDomains()->children()->GetAllDomainsResponse->children() as $domain) {
            $results[] = new ZimbraDomain ($domain);
        }

        return $results;
    }

    private function getAllServers($service = 'mailbox')
    {
        return $this->zimbraConnect->request('GetAllServersRequest', array(
            'service' => $service
        ));
    }

    public function getServers()
    {
        foreach ($this->getAllServers()->children()->GetAllServersResponse->children() as $server) {
            $results[] = new ZimbraServer($server);
        }

        return $results;
    }

    public function getServer($server, $by = 'name')
    {
        $params = array(
            'server' => array(
                '_'  => $server,
                'by' => $by,
            )
        );

        $response = $this->zimbraConnect->request('GetServerRequest', array(), $params);
        $servers = $response->children()->GetServerResponse->children();
        return new ZimbraServer($servers[0]);
    }

    public function getQuotas(array $attributes, $sort = null, $targetServer = null)
    {
        $results = array();

        if (!is_null($sort)) {
            $attributes['sortBy'] = $sort[0];
            $attributes['sortAscending'] = $sort[1];
        }

        $response = $this->getQuotaUsage($attributes, $targetServer);
        $quotas = $response->children()->GetQuotaUsageResponse->children();

        $systemUsers = array();
        foreach ($this->systemUsersRegexp as $user) {
            $a = explode('.', $user);
            $systemUsers[] = $a[0];
        }

        foreach ($quotas as $quota) {
            $account = explode('@', $quota['name']);
            $b = explode('.', $account[0]);

            if (!in_array($b[0], $systemUsers)) {
                $results[(string) $quota['id']] = array(
                    'name' => (string) $quota['name'],
                    'used' => (string) $quota['used'],
                    'limit' => (string) $quota['limit'],
                );
            }
        }

        return $results;
    }

    public function getTotalQuota($domain)
    {
        $result = array(
            'diskUsage'       => 0,
            'diskProvisioned' => 0,
            'mailTotal'       => 0,
        );

        foreach ($this->getAllServers()->children()->GetAllServersResponse->children() as $server) {

            $response = $this->getQuotaUsage(array('domain' => $domain), (string) $server['id']);
            $result['mailTotal'] += (string) $response->children()->GetQuotaUsageResponse['searchTotal'];

            $systemUsers = array();
            foreach ($this->systemUsersRegexp as $user) {
                $a = explode('.', $user);
                $systemUsers[] = $a[0];
            }

            foreach ($response->children()->GetQuotaUsageResponse->children() as $quota) {

                $b = explode('@', $quota['name']);
                $c = explode('.', $b[0]);
                $account = $c[0];

                // Remove the system users (antispam, etc.) from the count
                if (!in_array($account, $systemUsers)) {
                    $result['diskUsage'] += $quota['used'];
                    $result['diskProvisioned'] += $quota['limit'];
                } elseif ($b[0] = 'postmaster') {
                    $result['diskLimit'] = (int) $quota['limit'];
                    $result['mailTotal'] -= 1;
                } else {
                    $result['mailTotal'] -= 1;
                }
            }
        }

        $attrs = $this->getDomain($domain, 'name', array('zimbraDomainMaxAccounts'));
        $result['mailLimit'] = (int) $attrs[0];

        return $result;
    }

    public function createAccount($values)
    {
        $params = array();

        $params['name'] = $values['name'];
        unset($values['name']);

        $params['password'] = $values['password'];
        unset($values['password']);

        $params['attributes'] = $values;

        $response = $this->zimbraConnect->request('CreateAccountRequest', array(), $params);
        $accounts = $response->children()->CreateAccountResponse->children();

        return new ZimbraAccount($accounts[0]);
    }

    public function getAccount($domain, $by, $account)
    {
        $params = array(
            'account' => array(
                '_'  => $account,
                'by' => $by,
            )
        );

        $response = $this->zimbraConnect->request('GetAccountRequest', array(), $params);
        $accounts = $response->children()->GetAccountResponse->children();
        $account = new ZimbraAccount($accounts[0]);

        $aliases = $this->getAliases($domain);
        if (array_key_exists($account->name, $aliases)) {
            $account->setAliases($aliases[$account->name]);
        }

        return $account;
    }

    public function modifyAccount($values)
    {
        $params = array();
        $params['id'] = $values['id'];
        unset($values['id']);
        $params['attributes'] = $values;

        $response = $this->zimbraConnect->request('ModifyAccountRequest', array(), $params);
        $accounts = $response->children()->ModifyAccountResponse->children();

        return new ZimbraAccount($accounts[0]);
    }

    public function getAliases($domain)
    {
        $results = array();

        $response = $this->searchDirectory($domain, 0, 0, 'aliases');
        $aliases = $response->children()->SearchDirectoryResponse->children();

        foreach ($aliases as $alias) {
            $results[(string) $alias['targetName']][] = strstr($alias['name'], '@', true);
        }

        return $results;
    }

    public function getDomain($domain, $by = 'name', $attrs = array())
    {
        $attributes = array();

        if (!empty($attrs)) {
            $attributes['attrs'] = implode(',', $attrs);
        }

        $params = array(
            'domain' => array(
                '_'  => $domain,
                'by' => $by,
            )
        );

        $response = $this->zimbraConnect->request('GetDomainRequest', $attributes, $params);
        $domains = $response->children()->GetDomainResponse->children();

        return $domains[0]->children();
    }

    public function addAccountAlias($id, $alias)
    {
        $params = array(
            'id'    => $id,
            'alias' => $alias
        );

        $this->zimbraConnect->request('AddAccountAliasRequest', array(), $params);

        return true;
    }

    public function removeAccountAlias($id, $alias)
    {
        $params = array(
            'id'    => $id,
            'alias' => $alias
        );

        $this->zimbraConnect->request('RemoveAccountAliasRequest', array(), $params);

        return true;
    }

    public function getDistributionLists($domain)
    {
        $results = array();

        $response = $this->searchDirectory($domain, 0, 0, 'distributionlists');

        foreach ($response->children()->SearchDirectoryResponse->children() as $listData) {
            $results[] = new ZimbraDistributionList($listData);
        }

        return $results;
    }

    public function getDistributionList($list, $by = 'id')
    {
        $params = array(
            'dl' => array(
                '_'  => $list,
                'by' => $by,
            )
        );

        $response = $this->zimbraConnect->request('GetDistributionListRequest', array(), $params);
        $lists = $response->children()->GetDistributionListResponse->children();

        return new ZimbraDistributionList($lists[0]);
    }

    public function modifyDistributionList($values)
    {
        $params = array();
        $params['id'] = $values['id'];
        unset($values['id']);
        $params['attributes'] = $values;

        $response = $this->zimbraConnect->request('ModifyDistributionListRequest', array(), $params);
        $lists = $response->children()->ModifyDistributionListResponse->children();

        return new ZimbraDistributionList($lists[0]);
    }

    public function addDistributionListMember($id, $member)
    {
        $params = array(
            'id'    => $id,
            'dlm' => $member
        );

        $this->zimbraConnect->request('AddDistributionListMemberRequest', array(), $params);

        return true;
    }

    public function removeDistributionListMember($id, $member)
    {
        $params = array(
            'id'    => $id,
            'dlm' => $member
        );

        $this->zimbraConnect->request('RemoveDistributionListMemberRequest', array(), $params);

        return true;
    }

    public function createDistributionList($values)
    {
        $params = array();

        $params['name'] = $values['name'];
        unset($values['name']);

        $params['attributes'] = $values;

        $response = $this->zimbraConnect->request('CreateDistributionListRequest', array(), $params);
        $lists = $response->children()->CreateDistributionListResponse->children();

        return new ZimbraDistributionList($lists[0]);
    }

    public function deleteDistributionList($id)
    {
        $this->zimbraConnect->request('DeleteDistributionListRequest', array(), array('id' => $id));

        return true;
    }

    public function autoCompleteGal($domain, $name, $limit = 10)
    {
        $attributes = array(
            'domain' => $domain,
            'limit'  => $limit,
        );

        $response = $this->zimbraConnect->request('AutoCompleteGalRequest', $attributes, array('name' => $name));
        foreach ($response->children()->AutoCompleteGalResponse->children() as $cn) {
            foreach ($cn->children()->a as $a) {
                $result[(string) $a['n']] = (string) $a;
            }

            // Skip groups
            if (!isset($result['type'])) {
                $results[] = $result;
            }
        }

        return $results;
    }

    public function noOp()
    {
        $this->zimbraConnect->request('NoOpRequest');
    }

}
