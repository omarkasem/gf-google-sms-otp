function gf_google_clean_config(config) {
	var config = config.replace('const', '');
	var config = config.replace('=', '');
	var config = config.replace('firebaseConfig', '');
	var config = config.replace(';', '');
	return eval('(' + config + ')');
}

var firebaseConfig = firebase_data.firebaseConfig;
firebaseConfig = gf_google_clean_config(firebaseConfig);

// Initialize Firebase
firebase.initializeApp(firebaseConfig);
var ui = new firebaseui.auth.AuthUI(firebase.auth());
firebase.auth();
var uiConfig = {
	callbacks: {
		signInSuccessWithAuthResult: function (authResult, redirectUrl) {
			var uid = authResult.user.uid;
			var phone_number = authResult.user.phoneNumber;
			elnoor_register_user(uid, phone_number);
		},
		uiShown: function () {
			document.getElementsByClassName(
				'firebaseui-card-footer'
			)[0].style.display = 'none';
		},
	},
	// Will use popup for IDP Providers sign-in flow instead of the default, redirect.
	signInFlow: 'popup',
	signInSuccessUrl: null,
	signInOptions: [
		{
			provider: firebase.auth.PhoneAuthProvider.PROVIDER_ID,
		},
	],
};

if (
	typeof firebase_data.firebase_countries !== 'undefined' &&
	firebase_data.firebase_countries.length > 0
) {
	uiConfig.signInOptions[0].defaultCountry =
		firebase_data.firebase_countries[0];
	uiConfig.signInOptions[0].whitelistedCountries =
		firebase_data.firebase_countries;
}

ui.start('.gf_google_sms_otp', uiConfig);

function elnoor_register_user(uid, phone) {
	jQuery.ajax({
		type: 'POST',
		url: ajax_object.ajax_url,
		data: { action: 'elnoorRegisterUser', uid: uid, phone: phone },
		success: function (response) {
			location.reload();
		},
		error: function (error) {
			console.log(error);
		},
	});
}
