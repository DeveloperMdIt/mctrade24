class PPCGooglePayHandler
{
    static baseRequest = {
        apiVersion: 2,
        apiVersionMinor: 0
    };

    /**
     * @param {jquery} $
     */
    constructor($)
    {
        this.$               = $;
        this.locale          = 'de';
        this.paymentsClient  = null;
        this.fundingSource   = '';
        this.ppcJtl          = null;
        this.ppcConfig       = null;
        this.ppcOrderId      = '';
        this.cancelURL       = '#';
        this.stateURL        = '#';
        this.transactionInfo = { };
        this.isSandbox       = true;
        this.callbacks       = {};
        this.logger          = null;
    }

    /**
     * @param {object} params
     * @return void
     */
    init(params)
    {
        this.locale          = params.locale;
        this.fundingSource   = params.fundingSource;
        this.ppcOrderId      = params.orderId;
        this.cancelURL       = params.cancelURL;
        this.stateURL        = params.stateURL;
        this.transactionInfo = params.transactionInfo;
        this.isSandbox       = params.isSandbox !== false;
        this.callbacks       = params.callbacks;

        if (this.fundingSource !== '') {
            this.$('#complete-order-button').hide();
            this.$('#ppc-loading-spinner-confirmation').show();
        }
    }

    getConfig()
    {
        if (this.ppcConfig === null) {
            this.ppcConfig = this.ppcJtl.Googlepay().config();
        }

        return this.ppcConfig;
    }

    getPaymentsClient()
    {
        if (this.paymentsClient === null) {
            let env = {};

            env.environment     = this.isSandbox === false ? 'PRODUCTION' : 'TEST';
            this.paymentsClient = new google.payments.api.PaymentsClient(env);
        }

        return this.paymentsClient;
    }

    getTransactionInfo()
    {
        return this.transactionInfo;
    }

    async getPaymentDataRequest()
    {
        const googlePayConfig    = await this.getConfig();
        const paymentDataRequest = Object.assign({}, PPCGooglePayHandler.baseRequest);

        paymentDataRequest.allowedPaymentMethods = googlePayConfig.allowedPaymentMethods;
        paymentDataRequest.transactionInfo       = this.getTransactionInfo();
        paymentDataRequest.merchantInfo          = googlePayConfig.merchantInfo;

        return paymentDataRequest;
    }

    onGooglePayNotAvailable(data)
    {
        this.logger ? this.logger.debug('PPCGooglePay.onGooglePayNotAvailable', data) : null;
        if (typeof this.callbacks.onGooglePayNotAvailable === 'function') {
            this.callbacks.onGooglePayNotAvailable(data);
        }
    }

    onProcessPaymentError(data)
    {
        this.logger ? this.logger.debug('PPCGooglePay.onProcessPaymentError', data) : null;
        if (typeof this.callbacks.onProcessPaymentError === 'function') {
            this.callbacks.onProcessPaymentError(data);
        }
    }

    onPayerActionError(data)
    {
        this.logger ? this.logger.debug('PPCGooglePay.onPayerActionError', data) : null;
        if (typeof this.callbacks.onPayerActionError === 'function') {
            this.callbacks.onPayerActionError(data);
        }
    }

    /**
     * @param {object} ppc_jtl
     * @param {PPCPaymentLogger} logger
     * @return {Promise<void>}
     */
    async render(ppc_jtl, logger)
    {
        this.ppcJtl = ppc_jtl;
        this.logger = logger;

        this.logger.debug('PPCGooglePay.render', ppc_jtl);
        const paymentsClient      = this.getPaymentsClient();
        const isReadyToPayRequest = Object.assign({}, PPCGooglePayHandler.baseRequest);
        const googlePayConfig     = await this.getConfig();

        isReadyToPayRequest.allowedPaymentMethods = googlePayConfig.allowedPaymentMethods;

        try {
            let response = await paymentsClient.isReadyToPay(isReadyToPayRequest);

            if (response.result) {
                await this.addButton();
            } else {
                this.onGooglePayNotAvailable(response);
            }
        } catch (err) {
            this.onGooglePayNotAvailable(err);
        }
    }

    /**
     * @param {string} title
     * @param {string} message
     */
    cancelPayment(title, message)
    {
        eModal.setModalOptions({backdrop: 'static'});

        eModal.alert({
            message: message,
            title: title,
            onHide: () => {
                window.location.href = this.cancelURL;
            },
            buttons: [{
                text: 'OK',
                close: true,
                click: () => {
                    window.location.href = this.cancelURL;
                }
            }],
        });
    }

    async addButton()
    {
        const paymentsClient  = this.getPaymentsClient();
        const googlePayConfig = await this.getConfig();

        const button = paymentsClient.createButton({
            onClick:               () => this.onButtonClicked(),
            buttonSizeMode:        'fill',
            buttonType:            'order',
            buttonLocale:          this.locale,
            allowedPaymentMethods: googlePayConfig.allowedPaymentMethods
        });

        this.$('#ppc-loading-spinner-confirmation').hide();
        this.$('#paypal-button-container').append(button);
    }

    async processPayment(paymentData)
    {
        this.logger.debug('PPCGooglePay.processPayment', paymentData);
        try {
            const {status} = await this.ppcJtl.Googlepay().confirmOrder({
                orderId: this.ppcOrderId,
                paymentMethodData: paymentData.paymentMethodData,
            });

            if (status === "APPROVED") {
                return {
                    transactionState: 'SUCCESS'
                };
            } else if (status === "PAYER_ACTION_REQUIRED") {
                return {
                    transactionState: 'PAYER_ACTION_REQUIRED'
                };
            }

            return {
                transactionState: 'ERROR'
            };
        } catch (err) {
            return {
                transactionState: "ERROR",
                error: {
                    message: err.message,
                },
            };
        }
    }

    async onButtonClicked()
    {
        const paymentDataRequest = await this.getPaymentDataRequest();
        const paymentsClient     = this.getPaymentsClient();

        if (!this.$('form#complete_order')[0].checkValidity()) {
            return;
        }

        this.$('#ppc-loading-spinner-confirmation').show();
        this.$('#paypal-button-container').addClass('opacity-half');

        try {
            let paymentData = await paymentsClient.loadPaymentData(paymentDataRequest);
            await this.onPaymentAuthorized(paymentData);
        } catch (err) {
            if (err.statusCode && err.statusCode === 'CANCELED') {
                this.$('#ppc-loading-spinner-confirmation').hide();
                this.$('#paypal-button-container').removeClass('opacity-half');
            } else {
                this.logger.error('PPCGooglePay.onPaymentAuthorized', err);
                this.onGooglePayNotAvailable(err);
            }
        }
    }

    onCompleteOrder(data)
    {
        this.logger.debug('PPCGooglePay.onCompleteOrder', data);
        history.pushState(null, null, this.stateURL);
        let commentField       = this.$('#comment'),
            commentFieldHidden = this.$('#comment-hidden');
        if (commentField && commentFieldHidden) {
            commentFieldHidden.val(commentField.val());
        }
        this.$('form#complete_order').submit();
    }

    async onPaymentAuthorized(paymentData)
    {
        this.logger.debug('PPCGooglePay.onPaymentAuthorized', paymentData);

        let data = null;

        try {
            data = await this.processPayment(paymentData);
        } catch (errDetails) {
            this.logger.error('PPCGooglePay.onProcessPaymentError', errDetails);
            return this.onProcessPaymentError(errDetails);
        }

        data.eventHandled = false;
        this.$(window).trigger('ppc:buttonOnApprove', [data, null]);

        if (data.eventHandled) {
            return;
        }

        if (data.transactionState === "PAYER_ACTION_REQUIRED") {
            try {
                await this.ppcJtl.Googlepay().initiatePayerAction({orderId: this.ppcOrderId});
            } catch (err) {
                this.logger.error('PPCGooglePay.onPayerActionError', err);
                return this.onPayerActionError(err);
            }

            this.onCompleteOrder({transactionState: 'SUCCESS'});
        } else if (data.transactionState === 'SUCCESS') {
            this.onCompleteOrder(data);
        } else {
            this.onProcessPaymentError(data);
        }
    }
}