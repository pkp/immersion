{**
 * templates/frontend/components/loginForm.tpl
 *
 * Copyright (c) 2014-2020 Simon Fraser University
 * Copyright (c) 2003-2020 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @brief Display the basic login form fields
 *
 * @uses $loginUrl string URL to post the login request
 * @uses $source string Optional URL to redirect to after successful login
 * @uses $username string Username
 * @uses $password string Password
 * @uses $remember boolean Should logged in cookies be preserved on this computer
 * @uses $disableUserReg boolean Can users register for this site?
 *}

<form class="form-login" method="post" action="{$loginUrl}">
	{csrf}
	<input type="hidden" name="source" value="{$source|strip_unsafe_html|escape}"/>

	<fieldset>
		<div class="form-group form-group-username">
			<label for="usernameModal">
				{translate key="user.username"}
				<span class="required" aria-hidden="true">*</span>
				<span class="visually-hidden">
					{translate key="common.required"}
				</span>
			</label>
			<input type="text" class="form-control" name="username" id="usernameModal" value="{$username|default:""|escape}" maxlength="32" required>
		</div>
		<div class="form-group">
			<label for="passwordModal">
				{translate key="user.password"}
				<span class="required" aria-hidden="true">*</span>
				<span class="visually-hidden">
					{translate key="common.required"}
				</span>
			</label>
			<input type="password" class="form-control" name="password" id="passwordModal" value="{$password|default:""|escape}"
				maxlength="32" required>

			<div class="custom-control custom-checkbox">
				<input type="checkbox" class="custom-control-input" name="remember" id="rememberModal" value="1" checked="$remember">
				<label for="rememberModal" class="custom-control-label">
						{translate key="user.login.rememberUsernameAndPassword"}
				</label>
			</div>
		</div>

		{* recaptcha spam blocker *}
		{if $recaptchaPublicKey && \PKP\config\Config::getVar('captcha', 'captcha_on_login')}
			<div class="form-group">
				<fieldset class="recaptcha_wrapper">
					<div class="fields">
						<div class="recaptcha">
							<div class="g-recaptcha" data-sitekey="{$recaptchaPublicKey|escape}" data-theme="dark">
							</div><label for="g-recaptcha-response" style="display:none;" hidden>Recaptcha response</label>
						</div>
					</div>
				</fieldset>
			</div>
		{/if}

		{* altcha spam blocker *}
		{if $altchaEnabled}
			<fieldset class="altcha_wrapper">
				<div class="fields">
					<altcha-widget challengejson='{$altchaChallenge|@json_encode}' floating></altcha-widget>
				</div>
			</fieldset>
		{/if}

		<div class="form-group">
			<p>
				<button class="btn btn-primary" type="submit">
					{translate key="user.login"}
				</button>

				{if !$disableUserReg}
					{capture assign="registerUrl"}{url page="user" op="register" source=$source}{/capture}
					<a href="{$registerUrl}" class="btn btn-secondary">
						{translate key="user.login.registerNewAccount"}
					</a>
				{/if}
			</p>

			<p>
				<a href="{url page="login" op="lostPassword"}">
					{translate key="user.login.forgotPassword"}
				</a>
			</p>
		</div>
	</fieldset>
</form>
