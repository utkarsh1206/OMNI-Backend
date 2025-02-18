export const SMS_URL_METHODS = [
    {
        title: "GET",
        key: "get"
    },
    {
        title: "POST",
        key: "post"
    }
]

export const SMS_STATUS = [
    {
        title: "Enabled",
        key: true
    },
    {
        title: "Disabled",
        key: false
    }
]

export const SMS_CONTENT_TYPE = [
    {
        title: "JSON",
        key: "json"
    },
    {
        title: "Plain Text",
        key: "plain text"
    }
]

export const formReqPayload = (formData) => {
    return {
        provider_name: formData.provider_name,
        configurations: [
            {
                method: formData.method,
                type: formData.type,
                url: formData.url,
                query_params: formData.query_params,
                max_length: formData.max_length || undefined,
                success_code: formData.success_code,
                enabled: formData.enabled ? 'true' : 'false'
            }
        ]
    }
}