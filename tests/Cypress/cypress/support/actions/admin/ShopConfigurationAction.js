import AdminAPIClient from "Services/shopware/AdminAPIClient";

export default class ShopConfigurationAction {

    /**
     *
     */
    constructor() {
        this.apiClient = new AdminAPIClient();
    }


    /**
     *
     * @param {ShopConfiguration} shopConfig
     * @param {PluginConfiguration} pluginConfig
     */
    configureEnvironment(shopConfig, pluginConfig) {

        this._configureShop(shopConfig);

        this.configurePlugin(pluginConfig);

        this._clearCache();
    }

    /**
     *
     * @param {PluginConfiguration} pluginConfig
     * @returns {*}
     */
    configurePlugin(pluginConfig) {

        // assign all payment methods to
        // all available sales channels
        return this.apiClient.get('/sales-channel').then(channels => {

            if (channels === undefined || channels === null) {
                throw new Error('Attention, No Sales Channels found trough Shopware API');
            }

            let systemConfigData = {};

            const mollieConfig = this._buildMollieConfiguration(pluginConfig);

            // assign "all sales channels" to the configuration
            systemConfigData[null] = mollieConfig;

            channels.forEach(channel => {
                this._configureSalesChannel(channel.id);
                systemConfigData[channel.id] = mollieConfig;
            });

            this.apiClient.post('/_action/system-config/batch', systemConfigData).then(() => {
                this._clearCache();
            });

        });
    }

    /**
     *
     * @param {PluginConfiguration} pluginConfig
     * @private
     */
    _buildMollieConfiguration(pluginConfig) {
        return {
            "MolliePayments.config.testMode": true,
            "MolliePayments.config.debugMode": true,
            // ------------------------------------------------------------------
            "MolliePayments.config.shopwareFailedPayment": !pluginConfig.getMollieFailureMode(),
            "MolliePayments.config.enableCreditCardComponents": pluginConfig.getCreditCardComponents(),
            "MolliePayments.config.enableApplePayDirect": pluginConfig.getApplePayDirectEnabled(),
            "MolliePayments.config.oneClickPaymentsEnabled": false,
            "MolliePayments.config.paymentMethodBankTransferDueDateDays": 2,
            "MolliePayments.config.orderLifetimeDays": 4,
            // ------------------------------------------------------------------
            "MolliePayments.config.orderStateWithAAuthorizedTransaction": 'in_progress',
            "MolliePayments.config.orderStateWithAPaidTransaction": 'completed',
            "MolliePayments.config.orderStateWithAFailedTransaction": 'open',
            "MolliePayments.config.orderStateWithACancelledTransaction": 'cancelled',
            "MolliePayments.config.refundManagerEnabled": true,
            // ------------------------------------------------------------------
            "MolliePayments.config.subscriptionsEnabled": true,
            "MolliePayments.config.subscriptionsShowIndicator": pluginConfig.getSubscriptionIndicator(),
            "MolliePayments.config.subscriptionsAllowAddressEditing": true,
            "MolliePayments.config.subscriptionsAllowPauseResume": true,
            "MolliePayments.config.subscriptionsAllowSkip": true,
            // ---------------------------------------------------------------
            "MolliePayments.config.paypalExpressRestrictions": pluginConfig.getPaypalExpressRestrictions()
        };
    }

    /**
     *
     * @param {ShopConfiguration} shopConfiguration - The shop configuration object
     * @private
     */
    _configureShop(shopConfiguration) {
        const data = {};

        const config = {
            "core.loginRegistration.showAccountTypeSelection": true,
            "core.loginRegistration.requireDataProtectionCheckbox": shopConfiguration.getDataPrivacy(),
        };

        data[null] = config;

        this.apiClient.post('/_action/system-config/batch', data);
    }

    /**
     *
     * @param id
     * @private
     */
    _configureSalesChannel(id) {
        this.apiClient.get('/payment-method').then(payments => {

            if (payments === undefined || payments === null) {
                return;
                throw new Error('Attention, No payments trough Shopware API');
            }

            let paymentMethodsIds = [];

            payments.forEach(element => {
                paymentMethodsIds.push({
                    "id": element.id
                });
            });

            const data = {
                "id": id,
                "paymentMethods": paymentMethodsIds
            };

            this.apiClient.patch('/sales-channel/' + id, data);
        });
    }

    /**
     *
     * @returns {*}
     */
    _clearCache() {
        return this.apiClient.delete('/_action/cache').catch((err) => {
            console.log('Cache could not be cleared')
        });
    }

}
