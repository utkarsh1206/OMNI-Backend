import { jwtDecode } from 'jwt-decode';

const getUserPermissions = () => {
    const token = localStorage.getItem('token');
    if (token) {
        const decoded = jwtDecode(token);
        return decoded?.permission || [];
    }
    return [];
}

export default getUserPermissions;