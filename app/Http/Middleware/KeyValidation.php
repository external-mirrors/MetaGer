<?php

namespace App\Http\Middleware;

use Closure;
use Cookie;
use App\Models\Key;

//use KeyServiceProvider;

class KeyValidation
{
    protected $key;

    public function __construct(Key $key){
        $this->key = $key;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {   
        //dd($this->key->key, $this->key->getStatus());
        if($this->key->key !== '' && $this->key->getStatus()) {
            return response('valid key');
            //return $next($request);
        } elseif($this->key->key !== '' && !$this->key->getStatus()) {
            if($request->filled('key')){
                return response('invalid key (parameter)');
                //return redirect($request->except('key'));
            } else {
                Cookie::queue('key', '', 0, '/', null, false, false);
                return response('invalid key (cookie)');
                //return redirect($request);
            }
        } else {
            return response('no key');
            //return redirect($request);
        }
    }
}
