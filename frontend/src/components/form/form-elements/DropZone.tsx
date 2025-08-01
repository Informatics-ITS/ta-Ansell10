"use client";
import React, { useState } from "react";
import ComponentCard from "../../common/ComponentCard";
import { useDropzone, Accept } from "react-dropzone"; // Import Accept
import Papa from "papaparse"; // Import PapaParse untuk parsing CSV

const DropzoneComponent: React.FC<{ onDrop: (acceptedFiles: File[]) => void }> = ({ onDrop }) => {
  const [csvFile, setCsvFile] = useState<File | null>(null);
  const [csvData, setCsvData] = useState<any[]>([]); // State untuk menyimpan data CSV yang diparse
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  
  // Define accepted file types
  const fileAccept: Accept = {
    "text/csv": [], // Only CSV files allowed
  };

  const { getRootProps, getInputProps, isDragActive } = useDropzone({
    onDrop: (acceptedFiles) => handleDrop(acceptedFiles),
    accept: fileAccept, // Use fileAccept here
    multiple: false, // Allow only one file at a time
  });

  const handleDrop = (acceptedFiles: File[]) => {
    const file = acceptedFiles[0] || null;
    setCsvFile(file);
    setError(""); // Reset error
    setCsvData([]); // Reset CSV data

    // Parse the CSV file
    if (file) {
      parseCsv(file);
      onDrop(acceptedFiles);
    }
  };

  const parseCsv = (file: File) => {
    setLoading(true);
    Papa.parse(file, {
      complete: (result) => {
        console.log(result);
        setCsvData(result.data); // Set parsed data
        setLoading(false);
        console.log(file);
      },
      error: (err) => {
        setError("Error parsing CSV file");
        console.error(err);
        setLoading(false);
      },
    });
  };

  return (
    <ComponentCard title="Dropzone">
      <div
        {...getRootProps()}
        className={`transition border border-gray-300 border-dashed cursor-pointer dark:hover:border-brand-500 dark:border-gray-700 rounded-xl hover:border-brand-500 p-7 lg:p-10
          ${isDragActive ? "border-brand-500 bg-gray-100 dark:bg-gray-800" : "border-gray-300 bg-gray-50 dark:border-gray-700 dark:bg-gray-900"}`}
      >
        {/* Hidden Input */}
        <input {...getInputProps()} />
        <div className="dz-message flex flex-col items-center m-0!">
          {/* Icon Container */}
          <div className="mb-[22px] flex justify-center">
            <div className="flex h-[68px] w-[68px] items-center justify-center rounded-full bg-gray-200 text-gray-700 dark:bg-gray-800 dark:text-gray-400">
              <svg
                className="fill-current"
                width="29"
                height="28"
                viewBox="0 0 29 28"
                xmlns="http://www.w3.org/2000/svg"
              >
                <path
                  fillRule="evenodd"
                  clipRule="evenodd"
                  d="M14.5019 3.91699C14.2852 3.91699 14.0899 4.00891 13.953 4.15589L8.57363 9.53186C8.28065 9.82466 8.2805 10.2995 8.5733 10.5925C8.8661 10.8855 9.34097 10.8857 9.63396 10.5929L13.7519 6.47752V18.667C13.7519 19.0812 14.0877 19.417 14.5019 19.417C14.9161 19.417 15.2519 19.0812 15.2519 18.667V6.48234L19.3653 10.5929C19.6583 10.8857 20.1332 10.8855 20.426 10.5925C20.7188 10.2995 20.7186 9.82463 20.4256 9.53184L15.0838 4.19378C14.9463 4.02488 14.7367 3.91699 14.5019 3.91699ZM5.91626 18.667C5.91626 18.2528 5.58047 17.917 5.16626 17.917C4.75205 17.917 4.41626 18.2528 4.41626 18.667V21.8337C4.41626 23.0763 5.42362 24.0837 6.66626 24.0837H22.3339C23.5766 24.0837 24.5839 23.0763 24.5839 21.8337V18.667C24.5839 18.2528 24.2482 17.917 23.8339 17.917C23.4197 17.917 23.0839 18.2528 23.0839 18.667V21.8337C23.0839 22.2479 22.7482 22.5837 22.3339 22.5837H6.66626C6.25205 22.5837 5.91626 22.2479 5.91626 21.8337V18.667Z"
                />
              </svg>
            </div>
          </div>

          {/* Text Content */}
          <h4 className="mb-3 font-semibold text-gray-800 text-theme-xl dark:text-white/90">
            {isDragActive ? "Drop Files Here" : "Drag & Drop Files Here"}
          </h4>
          <span className="text-center mb-5 block w-full max-w-[290px] text-sm text-gray-700 dark:text-gray-400">
            Drag and drop your CSV file here or browse
          </span>
          <span className="font-medium underline text-theme-sm text-brand-500">Browse File</span>
        </div>
      </div>

      {/* Tampilkan Data CSV sebagai Tabel */}
      {csvData.length > 0 && (
        <div className="mt-6 overflow-x-auto">
          <table className="table-auto w-full border-collapse border border-gray-200">
            <thead>
              <tr>
                {csvData[0].map((header: string, index: number) => (
                  <th key={index} className="border px-4 py-2 text-left">{header}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {csvData.slice(1).map((row: any, rowIndex: number) => (
                <tr key={rowIndex}>
                  {row.map((cell: string, cellIndex: number) => (
                    <td key={cellIndex} className="border px-4 py-2">{cell}</td>
                  ))}
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      {error && <p className="text-red-500 text-sm mt-2">{error}</p>}
    </ComponentCard>
  );
};

export default DropzoneComponent;
