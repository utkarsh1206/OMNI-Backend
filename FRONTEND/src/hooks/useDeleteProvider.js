import { useState, useCallback } from 'react';
import { useToast } from '../context/ToastContext';
import axiosInstance from '../utils/axiosInstance';
import { getErrorMessage } from '../utils/errorHandlerUtils';

export const useDeleteData = (onDeleteSuccess, endPoint, successDeleteMessage) => {
    const [deleteRecordData, setDeleteRecordData] = useState({});
    const [openDeleteModal, setOpenDeleteModal] = useState(false);
    const showToast = useToast();

    const handleDelete = useCallback(async () => {
        try {
            await axiosInstance.delete(endPoint);
            showToast(successDeleteMessage, 'success');
            onDeleteSuccess(deleteRecordData.id);
        } catch (error) {
            showToast(getErrorMessage(error), 'danger');
        } finally {
            setOpenDeleteModal(false);
            setDeleteRecordData(null);
        }
    }, [deleteRecordData, onDeleteSuccess, showToast, endPoint, successDeleteMessage]);

    const openDeleteConfirmation = useCallback((id, name) => {
        setDeleteRecordData({ id, name });
        setOpenDeleteModal(true);
    }, []);

    return {
        deleteRecordData,
        openDeleteModal,
        setOpenDeleteModal,
        handleDelete,
        openDeleteConfirmation,
    };
};