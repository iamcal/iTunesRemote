
tell application "iTunes"
	set cur_track to current track
	tell cur_track to set {artworkData, imageFormat} to {(data of artwork 1), (format of artwork 1) as string} 
end tell

set ext to ".png"
set fileType to "PNG"
if imageFormat contains "JPEG" then
	set ext to ".jpg"
	set fileType to "JPEG"
end if

set fixed_file to ("/www/artwork_temp" & ext) as text

tell application "iTunes"
	set file_reference to (open for access fixed_file with write permission)
	set eof file_reference to 512
	write artworkData to file_reference starting at 513
	close access file_reference
end tell

-- tell application "Finder"
-- 	set file type of (fixed_file as string) to fileType
-- end tell

tell application "Image Events"
	launch
           
	set this_image to open fixed_file
	scale this_image to size 300

	set new_item to ("/www/artwork.png") as string
	save this_image in new_item as PNG
	close this_image
end tell 

do shell script "rm " & fixed_file