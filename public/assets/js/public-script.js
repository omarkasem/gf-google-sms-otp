const firebaseConfig = {
	apiKey: 'AIzaSyAfjlRL8TywSO_d0L-ifHKhQZ_oOFhOxNM',
	authDomain: 'test-b418a.firebaseapp.com',
	databaseURL: 'https://test-b418a.firebaseio.com',
	projectId: 'test-b418a',
	storageBucket: 'test-b418a.appspot.com',
	messagingSenderId: '588848921404',
	appId: '1:588848921404:web:bbd38db125fe0e631f7c39',
	measurementId: 'G-1HNC8DTDJB',
};

// Initialize Firebase
firebase.initializeApp(firebaseConfig);

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

var ui = new firebaseui.auth.AuthUI(firebase.auth());
firebase.auth().languageCode = 'it';
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
			defaultCountry: 'EG',
			whitelistedCountries: ['EG'],
		},
	],
};
ui.start('.gf_google_sms_otp', uiConfig);
