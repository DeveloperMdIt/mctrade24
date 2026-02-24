export const ioFetch = async (fnName, method = null, data = null) => {
    var dataWrapper = {
        name: fnName,
        params: [data, method],
    };

    const ioUrl = window.admProInstallerIoUrl;

    const formData = new FormData();
    formData.append("io", JSON.stringify(dataWrapper));
    formData.append("jtl_token", window.JTL_TOKEN);

    const response = await fetch(ioUrl, {
        method: "POST",
        body: formData,
    });

    if (!response.ok) {
        return response.json().then((err) => Promise.reject(err));
    }

    try {
        const responseData = await response.json();
        if (responseData?.error) {
            // Preserve server error as object when rejecting so callers can inspect it
            console.error(responseData?.error);
            return Promise.reject(responseData);
        }

        return responseData;
    } catch (error) {
        return Promise.reject(error);
    }
};
