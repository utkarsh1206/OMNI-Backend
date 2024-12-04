import React, { useState, useEffect } from "react";
import { CRow, CCol, CPagination, CPaginationItem, CFormSelect } from "@coreui/react";

const PaginationForReport = ({ currentPage, lastPage, onPageChange, total, perPage, setPerPage, setCurrentPage }) => {
  const maxVisiblePages = 5; // Maximum pages to show in pagination

  const renderPaginationItems = () => {
    const pages = [];
    if (lastPage <= maxVisiblePages) {
      // If the total number of pages is less than or equal to maxVisiblePages
      for (let page = 1; page <= lastPage; page++) {
        pages.push(
          <CPaginationItem
            key={page}
            active={page === currentPage}
            onClick={() => onPageChange(page)}
          >
            {page}
          </CPaginationItem>
        );
      }
    } else {
      // Logic for ellipsis
      let startPage = Math.max(1, currentPage - Math.floor(maxVisiblePages / 2));
      let endPage = Math.min(lastPage, startPage + maxVisiblePages - 1);

      if (endPage - startPage < maxVisiblePages - 1) {
        startPage = Math.max(1, endPage - maxVisiblePages + 1);
      }

      if (startPage > 1) {
        pages.push(
          <CPaginationItem key={1} onClick={() => onPageChange(1)}>
            1
          </CPaginationItem>
        );
        if (startPage > 2) {
          pages.push(
            <CPaginationItem key="start-ellipsis" disabled>
              ...
            </CPaginationItem>
          );
        }
      }

      for (let page = startPage; page <= endPage; page++) {
        pages.push(
          <CPaginationItem
            key={page}
            active={page === currentPage}
            onClick={() => onPageChange(page)}
          >
            {page}
          </CPaginationItem>
        );
      }

      if (endPage < lastPage) {
        if (endPage < lastPage - 1) {
          pages.push(
            <CPaginationItem key="end-ellipsis" disabled>
              ...
            </CPaginationItem>
          );
        }
        pages.push(
          <CPaginationItem key={lastPage} onClick={() => onPageChange(lastPage)}>
            {lastPage}
          </CPaginationItem>
        );
      }
    }
    return pages;
  };


  const calculateRange = () => {
    const startItem = (currentPage - 1) * perPage + 1;
    const endItem = Math.min(currentPage * perPage, total);
    return `Showing ${startItem} to ${endItem} of ${total}`;
  };

  return (
    <>
      {/* <CRow className="mt-3"> */}
        <CCol className="d-flex justify-content-start">
          <CPagination>
            <CPaginationItem
              disabled={currentPage === 1}
              onClick={() => onPageChange(currentPage - 1)}
            >
              Previous
            </CPaginationItem>
            {renderPaginationItems()}
            <CPaginationItem
              disabled={currentPage === lastPage}
              onClick={() => onPageChange(currentPage + 1)}
            >
              Next
            </CPaginationItem>
          </CPagination>
        </CCol>
        {/* <CCol className="d-flex justify-content-end align-items-baseline">
          <span className="me-2">PerPage Display:</span>
          <CFormSelect
            id="perPage"
            name="perPage"
            value={perPage}
            onChange={(e) => {
              setPerPage(Number(e.target.value));
              setCurrentPage(1); // Reset to the first page
            }}
            className="me-2"
            size="sm"
            style={{ width: '80px' }}
          >
            <option key="5" value="5">
              5
            </option>
            <option key="10" value="10">
              10
            </option>
            <option key="15" value="15">
              15
            </option>
            <option key="20" value="20">
              20
            </option>
            <option key="50" value="50">
              50
            </option>
            <option key="100" value="100">
              100
            </option>
          </CFormSelect>
        </CCol> */}
      {/* </CRow> */}

      {/* Display total items and current range */}
      {/* <CRow className="mb-2">
        <CCol className="text-center">
        {calculateRange()}
        </CCol>
      </CRow> */}
    </>
  );
};
export default PaginationForReport;
