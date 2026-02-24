class PPCPaymentLogger
{
    static LOGLEVEL_ERROR  = 1;
    static LOGLEVEL_NOTICE = 2;
    static LOGLEVEL_DEBUG  = 3;

    /**
     * @param {string} paymentClass
     * @param {int} logLevel
     */
    constructor(paymentClass, logLevel)
    {
        this.paymentClass = paymentClass;
        this.logLevel     = logLevel ? logLevel : PPCPaymentLogger.LOGLEVEL_ERROR;
    }

    /**
     * @param {int} level
     * @param {string} msg
     * @param {(object|null)} data
     * @return {Promise<void>}
     */
    async log(level, msg, data)
    {
        if (this.logLevel < level) {
            return;
        }

        let context = { };
        let params  = {
            paymentClass: this.paymentClass,
            data: data,
        };

        ppcpIOManagedCall('jtl_paypal_commerce.logState', [level, msg, params], context);
    }

    /**
     * @param {string} msg
     * @param {(object|null)} data
     * @return {Promise<void>}
     */
    async debug(msg, data)
    {
        return this.log(PPCPaymentLogger.LOGLEVEL_DEBUG, msg, data);
    }

    /**
     * @param {string} msg
     * @param {(object|null)} data
     * @return {Promise<void>}
     */
    async warn(msg, data)
    {
        return this.log(PPCPaymentLogger.LOGLEVEL_NOTICE, msg, data);
    }

    /**
     * @param {string} msg
     * @param {(object|null)} data
     * @return {Promise<void>}
     */
    async err(msg, data)
    {
        return this.log(PPCPaymentLogger.LOGLEVEL_ERROR, msg, data);
    }
}