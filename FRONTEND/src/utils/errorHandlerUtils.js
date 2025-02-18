export const getErrorMessage = (error) => {
    const errorCode = error?.response?.status;
    if (errorCode && errorCode >= 400 && errorCode < 500) {
        return error?.response?.data?.message || "An unknown error occurred. Please try again.";
    }

    // For 5xx errors or unhandled errors, show a generic error message
    return "Something went wrong, please try again later.";
}