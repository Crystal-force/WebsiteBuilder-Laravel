<?php

namespace Common\Domains;

use Auth;
use Common\Core\BaseController;
use Common\Core\HttpClient;
use Common\Database\Paginator;
use Common\Domains\Actions\DeleteCustomDomains;
use Common\Settings\Settings;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;

class CustomDomainController extends BaseController
{
    /**
     * @var CustomDomain
     */
    private $customDomain;

    /**
     * @var Request
     */
    private $request;

    /**
     * @param CustomDomain $customDomain
     * @param Request $request
     */
    public function __construct(CustomDomain $customDomain, Request $request)
    {
        $this->customDomain = $customDomain;
        $this->request = $request;
    }

    /**
     * @return Response
     */
    public function index()
    {
        $userId = $this->request->get('userId');
        $this->authorize('index', [CustomDomain::class, $userId]);

        $paginator = new Paginator($this->customDomain, $this->request->all());
        $paginator->searchColumn = 'host';

        if ($userId) {
            $paginator->where('user_id', $userId);
        } else {
            $paginator->with('user');
        }

        return $this->success(['pagination' => $paginator->paginate()]);
    }

    /**
     * @return Response
     */
    public function store()
    {
        $this->authorize('store', CustomDomain::class);

        $this->validate($this->request, [
            'host' => 'required|string|max:100|unique:custom_domains',
            'global' => 'boolean',
        ]);

        $domain = $this->customDomain->create([
            'host' => $this->request->get('host'),
            'user_id' => Auth::id(),
            'global' => $this->request->get('global', false),
        ]);

        return $this->success(['domain' => $domain]);
    }

    /**
     * @param CustomDomain $customDomain
     * @return Response
     */
    public function update(CustomDomain $customDomain)
    {
        $this->authorize('store', $customDomain);

        $this->validate($this->request, [
            'host' => ['string', 'max:100', Rule::unique('custom_domains')->ignore($customDomain->id)],
            'global' => 'boolean',
            'resource_id' => 'integer',
            'resource_type' => 'string',
        ]);

        $data = $this->request->all();
        $data['user_id'] = Auth::id();
        $data['global'] = $this->request->get('global', $customDomain->global);
        $domain = $customDomain->update($data);

        return $this->success(['domain' => $domain]);
    }

    /**
     * @param string $ids
     * @return Response
     */
    public function destroy($ids)
    {
        $domainIds = explode(',', $ids);
        $this->authorize('destroy', [CustomDomain::class, $domainIds]);

        app(DeleteCustomDomains::class)->execute($domainIds);

        return $this->success();
    }

    public function authorizeCrupdate()
    {
        $this->authorize('store', CustomDomain::class);

        $domainId = $this->request->get('domainId');

        $this->validate($this->request, [
            'host' => ['required', 'string', 'max:100', Rule::unique('custom_domains')->ignore($domainId)],
        ]);

        return $this->success([
            'serverIp' => env('SERVER_IP') ? : $_SERVER['SERVER_ADDR']
        ]);
    }

    /**
     * Proxy method for validation on frontend to avoid cross-domain issues.
     *
     * @param HttpClient $http
     * @return JsonResponse
     */
    public function validateDomainApi(HttpClient $http)
    {
        $this->validate($this->request, [
            'host' => 'required|string',
        ]);

        $host = $this->request->get('host');
        try {
            return $http->get("$host/secure/custom-domain/validate/2BrM45vvfS");
        } catch (RequestException $e) {
            return $this->error();
        }
    }

    /**
     * Method for validating if CNAME for custom domain was attached properly.
     * @return Response
     */
    public function validateDomain()
    {
        return $this->success(['result' => 'connected']);
    }
}
