import { useState, useRef } from "react";
import ReactQuill from "react-quill";
import 'react-quill/dist/quill.snow.css';
import { CPopover, CListGroup, CListGroupItem } from "@coreui/react";

const QuillTextEditor = ({ handleEditorChange, editorValue }) => {
    const [isVariableMenuVisible, setVariableMenuVisibility] = useState(false);
    const [cursorPosition, setCursorPosition] = useState(0);
    const [popoverPosition, setPopoverPosition] = useState({ top: 0, left: 0 });
    const quillRef = useRef(null);

    const variables = ['senderName', "sentBy", 'email', 'number', 'date'];

    const modules = {
        toolbar: [
            [{ 'header': [] }, { 'font': [] }],
            [{ 'list': 'ordered' }, { 'list': 'bullet' }],
            [{ 'align': [] }],
            ['bold', 'italic', 'underline'],
            ['link'],
            [{ 'color': [] }, { 'background': [] }],
            ['image'],
            [{ 'indent': '-1' }, { 'indent': '+1' }],
            ['blockquote', 'code-block'],
            ['clean']  // Clean button to remove formatting
        ]
    };

    const handleSelectionChange = (range) => {
        if (range) {
            const quill = quillRef.current.getEditor();
            const plainText = quill.getText();
            const position = range.index;
            setCursorPosition(position);
            const cursorBounds = quill.getBounds(range);

            // Check if the last character is '@' and show the menu
            if (plainText[position - 1] === '@') {
                setVariableMenuVisibility(true);
                setPopoverPosition({
                    top: cursorBounds.top + cursorBounds.height,
                    left: cursorBounds.left,
                });
            } else {
                setVariableMenuVisibility(false);
            }
        }
    };

    const handleVariableSelection = (variable) => {
        variable = `{{ ${variable.toUpperCase()} }}`;
        const quill = quillRef.current.getEditor();
        const position = cursorPosition;

        quill.deleteText(position - 1, 1);
        // Insert the selected variable at the cursor position
        quill.insertText(position - 1, variable);

        // Set the cursor back to the correct position after the text insertion
        quill.setSelection(position + variable.length, 0);

        // Hide the variable menu after insertion
        setVariableMenuVisibility(false);
    };

    return (
        <div style={{ position: 'relative' }}>
            <ReactQuill
                modules={modules}  // Apply custom toolbar
                ref={quillRef}
                value={editorValue}
                onChange={handleEditorChange}
                onChangeSelection={handleSelectionChange}
                placeholder="Write your email template here..."
            />

            {isVariableMenuVisible && (
                <CPopover
                    placement="bottom"
                    visible={isVariableMenuVisible}
                    style={{ "--cui-popover-font-size": "1.25rem" }}
                    content={
                        <CListGroup style={{ "--cui-list-group-border-width": "0px" }}>
                            {variables.map((variable, index) => (
                                <CListGroupItem
                                    key={index}
                                    as="a"
                                    action
                                    onClick={() => handleVariableSelection(variable)}
                                    style={{ cursor: 'pointer' }}
                                >
                                    {variable}
                                </CListGroupItem>
                            ))}
                        </CListGroup>
                    }
                >
                    <div
                        style={{
                            position: 'absolute',
                            top: popoverPosition.top + 60,
                            left: popoverPosition.left + 10
                        }}
                    >{' '}</div>
                </CPopover>
            )}
        </div>
    )
}

export default QuillTextEditor;